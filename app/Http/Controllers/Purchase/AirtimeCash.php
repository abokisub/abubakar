<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class  AirtimeCash extends Controller
{

    public function Convert(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        $transid = $this->purchase_ref('AIRTIME2CASH_');
        $validator = Validator::make($request->all(), [
            'network' => 'required',
            'sender_number' => 'required|numeric|digits:11',
            'bypass' => 'required',
            'payment_type' => 'required',
            'amount' => 'required|numeric|integer|not_in:0|gt:0',
        ]);
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
                    if (DB::table('block')->where(['number' => $request->sender_number])->count() == 0) {
                        if (DB::table('cash')->where('transid', $transid)->count() == 0 and DB::table('message')->where('transid', $transid)->count() == 0) {
                            $phone = $request->sender_number;
                            // check number
                            if ($request->bypass == false || $request->bypass == 'false') {
                                $validate = substr($phone, 0, 4);
                                if ($request->network == "MTN") {
                                    if (strpos(" 0702 0703 0713 0704 0706 0716 0802 0803 0806 0810 0813 0814 0816 0903 0913 0906 0916 0804 ", $validate) == FALSE || strlen($phone) != 11) {
                                        return response()->json([
                                            'status' => 'fail',
                                            'message' => 'This is not a MTN Number => ' . $phone
                                        ])->setStatusCode(403);
                                    } else {
                                        $adex_bypass = true;
                                    }
                                } else if ($request->network == "GLO") {
                                    if (strpos(" 0805 0705 0905 0807 0907 0707 0817 0917 0717 0715 0815 0915 0811 0711 0911 ", $validate) == FALSE || strlen($phone) != 11) {
                                        return response()->json([
                                            'status' => 'fail',
                                            'message' => 'This is not a GLO Number =>' . $phone
                                        ])->setStatusCode(403);
                                    } else {
                                        $adex_bypass = true;
                                    }
                                } else if ($request->network == "AIRTEL") {
                                    if (strpos(" 0904 0802 0902 0702 0808 0908 0708 0918 0818 0718 0812 0912 0712 0801 0701 0901 0907 0917 ", $validate) == FALSE || strlen($phone) != 11) {
                                        return response()->json([
                                            'status' => 'fail',
                                            'message' => 'This is not a AIRTEL Number => ' . $phone
                                        ])->setStatusCode(403);
                                    } else {
                                        $adex_bypass = true;
                                    }
                                } else if ($request->network == "9MOBILE") {
                                    if (strpos(" 0809 0909 0709 0819 0919 0719 0817 0917 0717 0718 0918 0818 0808 0708 0908 ", $validate) == FALSE || strlen($phone) != 11) {
                                        return response()->json([
                                            'status' => 'fail',
                                            'message' => 'This is not a 9MOBILE Number => ' . $phone
                                        ])->setStatusCode(403);
                                    } else {
                                        $adex_bypass = true;
                                    }
                                } else {
                                    return response()->json([
                                        'status' => 'fail',
                                        'message' => 'Unable to get Network Name'
                                    ])->setStatusCode(403);
                                }
                            } else {
                                $adex_bypass = true;
                            }
                            //check if phone number is validated
                            if (substr($phone, 0, 1) == 0) {
                                // if bypassed
                                if ($adex_bypass == true) {
                                    // check user daly limit
                                    $all_limit = DB::table('message')->where(['username' => $user->username])->where(function ($query) {
                                        $query->where('role', '!=', 'credit');
                                        $query->where('role', '!=', 'debit');
                                        $query->where('role', '!=', 'upgrade');
                                        $query->where('plan_status', '!=', 2);
                                    })->whereDate('adex_date', Carbon::now())->sum('amount');
                                    if ($all_limit <= $user->user_limit) {
                                        if ((strtolower($request->payment_type) == 'wallet') xor strtolower($request->payment_type) == 'bank') {
                                            if ($request->network == '9MOBILE') {
                                                $network_name = 'mobile';
                                            } else {
                                                $network_name = strtolower($request->network);
                                            }
                                            if (DB::table('user_bank')->where('username', $user->username)->count() > 0 || $user->type != 'WITHDRAW') {
                                                $system_admin = DB::table('cash_discount')->first();
                                                $credit = ($request->amount / 100) * $system_admin->$network_name;
                                                $trans_history = [
                                                    'username' => $user->username,
                                                    'amount' => $credit,
                                                    'message' => 'Airtime to cash on process',
                                                    'oldbal' => $user->bal,
                                                    'newbal' => $user->bal,
                                                    'adex_date' => $this->system_date(),
                                                    'plan_status' => 0,
                                                    'transid' => $transid,
                                                    'role' => 'cash'
                                                ];
                                                $trans_cash = [
                                                    'username' => $user->username,
                                                    'amount' => $request->amount,
                                                    'amount_credit' => $credit,
                                                    'newbal' => $user->bal,
                                                    'oldbal' => $user->bal,
                                                    'transid' => $transid,
                                                    'network' => $request->network,
                                                    'payment_type' => strtoupper($request->payment_type),
                                                    'plan_status' => 0,
                                                    'plan_date' => $this->system_date(),
                                                    'system' => $system,
                                                    'sender_number' => $request->sender_number
                                                ];
                                                if ($this->inserting_data('message', $trans_history) and $this->inserting_data('cash', $trans_cash)) {

                                                    $send_message = $user->username . " want to convert " . $request->network . " ₦" . number_format($request->amount, 2) . " to cash. payment method is (" . strtoupper($request->payment_type) . "), Amount to Be Credited is ₦" . number_format($credit, 2) . " Airtime sent from " . $request->sender_number . " Reference is => " . $transid;
                                                    $mes_data = [
                                                        'mes' => $send_message,
                                                        'title' => 'AIRTIME 2 CASH'
                                                    ];
                                                    ApiSending::ADMINEMAIL($mes_data);
                                                    DB::table('request')->insert(['username' => $user->username, 'message' => $send_message, 'date' => $this->system_date(), 'transid' => $transid, 'status' => 0, 'title' => 'AIRTIME 2 CASH']);
                                                    return response()->json([
                                                        'status' => 'success',
                                                        'message' => 'Transaction On Process',
                                                        'request-id' => $transid,
                                                        'transid' => $transid,
                                                        'amount_credited' => $credit
                                                    ]);
                                                } else {
                                                    DB::table('message')->where(['transid' => $transid, 'username' => $user->username])->delete();
                                                    DB::table('cash')->where(['transid' => $transid, 'username' => $user->username])->delete();
                                                    return response()->json([
                                                        'status' => 'fail',
                                                        'message' => 'unable to insert infomation'
                                                    ])->setStatusCode(403);
                                                }
                                            } else {
                                                return response()->json([
                                                    'status' => 'fail',
                                                    'message' => 'Add Your Account Number (Kindly check settings)'
                                                ])->setStatusCode(403);
                                            }
                                        } else {
                                            return response()->json([
                                                'status' => 'fail',
                                                'message' => 'payment type unknown'
                                            ])->setStatusCode(403);
                                        }
                                    } else {
                                        return response()->json([
                                            'status' => 'fail',
                                            'message' => 'You  have reach Daily Transaction Limit Kindly message the admin for upgrade'
                                        ])->setStatusCode(403);
                                    }
                                } else {
                                    return response()->json([
                                        'status' => 'fail',
                                        'message' => 'Unable to bypass system account'
                                    ])->setStatusCode(403);
                                }
                            } else {
                                return response()->json([
                                    'status' => 'fail',
                                    'message' => 'invalid phone number => ' . $phone
                                ])->setStatusCode(403);
                            }
                        } else {
                            return response()->json([
                                'status' => 'fail',
                                'message' => 'Kindly Retry again'
                            ])->setStatusCode(403);
                        }
                    } else {
                        return response()->json([
                            'status' => 'fail',
                            'message' => 'Number Block'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'status' => 'fail',
                        'message' => 'Invalid Access Token'
                    ])->setStatusCode(403);
                }
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Access Token Required'
            ])->setStatusCode(403);
        }
    }
}
