<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class  ExamPurchase extends Controller
{

    public function ExamPurchase(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        $validator = Validator::make($request->all(), [
            'exam' => 'required',
            'quantity' => 'required|numeric|integer|not_in:0|gt:0|min:1|max:5',
        ]);
        $transid = $this->purchase_ref('RESULTCHECKER_');
        if (in_array($request->headers->get('origin'), $explode_url)) {
            $system = env('APP_NAME');
            if ($this->core()->allow_pin == 1) {
                // transaction pin required
                $check = DB::table('user')->where(['id' => $this->verifytoken($request->token), 'pin' => $request->pin]);
                if ($check->count() == 1) {
                    $det = $check->first();
                    $accessToken =  $det->apikey;
                } else {
                    return response()->json([
                        'status' => 'fail',
                        'message' => 'Invalid Transaction Pin'
                    ])->setStatusCode(403);
                }
            } else {
                // transaction pin not required
                $check = DB::table('user')->where(['id' => $this->verifytoken($request->token)]);
                if ($check->count() == 1) {
                    $det = $check->first();
                    $accessToken =  $det->apikey;
                } else {
                    return response()->json([
                        'status' => 'fail',
                        'message' => 'An Error Occur'
                    ])->setStatusCode(403);
                }
            }
        } else if (env('ADEX_DEVICE_KEY') == $request->header('Authorization')) {


            $system = "APP";

            if (DB::table('user')->where(['id' => $this->verifyapptoken($request->user_id), 'status' => 1])->count() == 1) {
                $d_token = DB::table('user')->where(['id' => $this->verifyapptoken($request->user_id), 'status' => 1])->first();
                $accessToken = $d_token->apikey;
            } else {
                $accessToken = 'null';
            }
        } else {
            $system = "API";
            $d_token = $request->header('Authorization');
            $accessToken = trim(str_replace("Token", "", $d_token));
        }
        if ($accessToken) {
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 'fail'
                ])->setStatusCode(403);
            } else {
                $user_check = DB::table('user')->where(['apikey' => $accessToken, 'status' => 1]);
                if ($user_check->count() == 1) {
                    $user = $user_check->first();
                    // check exam id
                    if (DB::table('exam_id')->where('plan_id', $request->exam)->count() == 1) {
                        $exam = DB::table('exam_id')->where('plan_id', $request->exam)->first();
                        $exam_name = strtolower($exam->exam_name);
                        // check if lock
                        $exam_lock = DB::table('cable_result_lock')->first();
                        if ($exam_lock->$exam_name == 1) {
                            $result_price = DB::table('result_charge')->first();
                            $exam_price = $result_price->$exam_name * $request->quantity;
                            if (DB::table('exam')->where('transid', $transid)->count() == 0 && DB::table('message')->where('transid', $transid)->count() == 0) {
                                DB::beginTransaction();
                                $user = DB::table('user')->where(['id' => $user->id])->lockForUpdate()->first();
                                if ($user->bal > 0) {
                                    if ($user->bal >= $exam_price) {
                                        $debit = $user->bal - $exam_price;
                                        $refund = $debit + $exam_price;
                                        if (DB::table('user')->where(['id' => $user->id])->update(['bal' => $debit])) {
                                        DB::commit();
                                            $trans_history = [
                                                'username' => $user->username,
                                                'amount' => $exam_price,
                                                'message' => strtoupper($exam_name) . ' Exam Pin Is On Process',
                                                'oldbal' => $user->bal,
                                                'newbal' => $debit,
                                                'adex_date' => $this->system_date(),
                                                'plan_status' => 0,
                                                'transid' => $transid,
                                                'role' => 'exam'
                                            ];
                                            $exam_history = [
                                                'username' => $user->username,
                                                'amount' => $exam_price,
                                                'plan_status' => 0,
                                                'plan_date' => $this->system_date(),
                                                'transid' => $transid,
                                                'exam_name' => strtoupper($exam_name),
                                                'oldbal' => $user->bal,
                                                'newbal' => $debit,
                                                'quantity' => $request->quantity,
                                                'purchase_code' => null,
                                            ];
                                            if (DB::table('exam')->insert($exam_history) and DB::table('message')->insert($trans_history)) {
                                                $exam_sel = DB::table('exam_sel')->first();
                                                $exam_vend = $exam_sel->$exam_name;
                                                $send_data = [
                                                    'transid' => $transid,
                                                    'username' => $user->username
                                                ];
                                                $response = ExamSend::$exam_vend($send_data);
                                                if ($response == 'success') {
                                                    DB::table('exam')->where('transid', $transid)->update(['plan_status' => 1]);
                                                    DB::table('message')->where('transid', $transid)->update(['plan_status' => 1,  'message' => strtoupper($exam_name) . ' Exam Pin Generated']);
                                                    $sendbank = DB::table('exam')->where('transid', $transid)->first();
                                                    return response()->json([
                                                        'username' => $user->username,
                                                        'amount' => $exam_price,
                                                        'transid' => $transid,
                                                        'quantity' => $request->quantity,
                                                        'message' => strtoupper($exam_name) . ' Exam Pin Generated',
                                                        'oldbal' => $user->bal,
                                                        'newbal' => $refund,
                                                        'date' => $this->system_date(),
                                                        'status' => 'success',
                                                        'request-id' => $transid,
                                                        'pin' => $sendbank->purchase_code
                                                    ]);
                                                } else if ($response == 'process') {
                                                    return response()->json([
                                                        'username' => $user->username,
                                                        'amount' => $exam_price,
                                                        'quantity' => $request->quantity,
                                                        'message' => strtoupper($exam_name) . ' Exam Pin Is On Process',
                                                        'oldbal' => $user->bal,
                                                        'newbal' => $debit,
                                                        'date' => $this->system_date(),
                                                        'status' => 'process',
                                                        'request-id' => $transid,
                                                    ]);
                                                } else if ($response == 'fail') {
                                                    DB::table('user')->where(['username' => $user->username, 'id' => $user->id])->update(['bal' => $refund]);
                                                    DB::table('exam')->where('transid', $transid)->update(['plan_status' => 2, 'newbal' => $refund]);
                                                    DB::table('message')->where('transid', $transid)->update(['plan_status' => 2, 'newbal' => $refund, 'message' => 'Transaction Fail']);
                                                    return response()->json([
                                                        'username' => $user->username,
                                                        'amount' => $exam_price,
                                                        'quantity' => $request->quantity,
                                                        'message' => 'Transaction Fail',
                                                        'oldbal' => $user->bal,
                                                        'newbal' => $refund,
                                                        'date' => $this->system_date(),
                                                        'status' => 'fail',
                                                        'request-id' => $transid,
                                                    ]);
                                                } else {
                                                    return response()->json([
                                                        'username' => $user->username,
                                                        'amount' => $exam_price,
                                                        'quantity' => $request->quantity,
                                                        'message' => strtoupper($exam_name) . ' Exam Pin Is On Process',
                                                        'oldbal' => $user->bal,
                                                        'newbal' => $debit,
                                                        'date' => $this->system_date(),
                                                        'status' => 'process',
                                                        'request-id' => $transid,
                                                    ]);
                                                }
                                            }
                                        }
                                    } else {
                                        return response()->json([
                                            'status' => 'fail',
                                            'message' => 'Insufficient Account Kindly fund your wallet => ₦' . number_format($user->bal, 2)
                                        ])->setStatusCode(403);
                                    }
                                } else {
                                    return response()->json([
                                        'status' => 'fail',
                                        'message' => 'Insufficient Account Kindly fund your wallet => ₦' . number_format($user->bal, 2)
                                    ])->setStatusCode(403);
                                }
                            } else {
                                return response()->json([
                                    'status' => 'fail',
                                    'message' => 'Referrence ID Used'
                                ])->setStatusCode(403);
                            }
                        } else {
                            return response()->json([
                                'status' => 'fail',
                                'message' => strtoupper($exam_name) . ' Not Available Right Now'
                            ])->setStatusCode(403);
                        }
                    } else {
                        return response()->json([
                            'status' => 'fail',
                            'message' => 'Invalid Exam ID'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'status' => 'fail',
                        'message' => 'Invalid Authorization Token'
                    ])->setStatusCode(403);
                }
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Authorization Token Required'
            ])->setStatusCode(403);
        }
    }
}
