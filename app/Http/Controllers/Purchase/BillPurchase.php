<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class  BillPurchase extends Controller
{
    public function Buy(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            $validator = Validator::make($request->all(), [
                'disco' => 'required',
                'meter_number' => 'required',
                'bypass' => 'required',
                'meter_type' => 'required',
                'amount' => 'required|numeric|integer|not_in:0|gt:0'
            ]);
            $system = env('APP_NAME');
            $transid = $this->purchase_ref('BILL_');
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

            $validator = Validator::make($request->all(), [
                'disco' => 'required',
                'meter_number' => 'required',
                'bypass' => 'required',
                'meter_type' => 'required',
                'amount' => 'required|numeric|integer|not_in:0|gt:0',
                'user_id' => 'required'
            ]);
            $system = "APP";
            $transid = $this->purchase_ref('BILL_');
            if (DB::table('user')->where(['id' => $this->verifyapptoken($request->user_id), 'status' => 1])->count() == 1) {
                $d_token = DB::table('user')->where(['id' => $this->verifyapptoken($request->user_id), 'status' => 1])->first();
                $accessToken = $d_token->apikey;
            } else {
                $accessToken = 'null';
            }
        } else {
            // api verification
            $validator = Validator::make($request->all(), [
                'disco' => 'required',
                'meter_number' => 'required',
                'bypass' => 'required',
                'meter_type' => 'required',
                'amount' => 'required|numeric|integer|not_in:0|gt:0',
                'request-id' => 'required|unique:bill,transid'
            ]);
            $system = "API";
            $id = "request-id";
            $transid = $request->$id;
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
                    if (DB::table('block')->where(['number' => $request->meter_number])->count() == 0) {
                        if (DB::table('bill')->where('transid', $transid)->count() == 0 and DB::table('message')->where('transid', $transid)->count() == 0) {
                            if (DB::table('bill_plan')->where(['plan_id' => $request->disco,  'plan_status' => 1])->count() == 1) {
                                $bill_plan = DB::table('bill_plan')->where(['plan_id' => $request->disco,  'plan_status' => 1])->first();
                                if ($this->core()->bill == 1) {
                                    $all_limit = DB::table('message')->where(['username' => $user->username])->where(function ($query) {
                                        $query->where('role', '!=', 'credit');
                                        $query->where('role', '!=', 'debit');
                                        $query->where('role', '!=', 'upgrade');
                                        $query->where('plan_status', '!=', 2);
                                    })->whereDate('adex_date', Carbon::now())->sum('amount');

                                    if ($this->core()->allow_limit == 1 && $user->kyc == 0) {
                                        if ($all_limit + $request->amount <= $user->user_limit) {
                                            $adex_new_go = true;
                                        } else {
                                            $adex_new_go = false;
                                        }
                                    } else {
                                        $adex_new_go = true;
                                    }

                                    if ($adex_new_go == true) {
                                        DB::beginTransaction();
                                        $user = DB::table('user')->where(['id' => $user->id])->lockForUpdate()->first();
                                        if (is_numeric($user->bal)) {
                                            if ($user->bal > 0) {
                                                if ((is_numeric($request->amount)) && $request->amount > 0) {
                                                    $bill_d = DB::table('bill_charge')->first();
                                                    if ($bill_d->bill_max >= $request->amount) {
                                                        if ($request->amount >= $bill_d->bill_min) {
                                                            if ($bill_d->direct == 1) {
                                                                $charges = $bill_d->bill;
                                                            } else {
                                                                $charges = ($request->amount / 100) * $bill_d->bill;
                                                            }
                                                            $total_amount = $charges + $request->amount;
                                                            if ($user->bal >= $total_amount) {
                                                                $debit = $user->bal - $total_amount;
                                                                $refund = $debit + $total_amount;
                                                                $bill_sel = DB::table('bill_sel')->first();
                                                                $adm = new MeterSend();
                                                                $check_now = $bill_sel->bill;
                                                                $sending_data = [
                                                                    'disco' => $request->disco,
                                                                    'meter_type' => strtolower($request->meter_type),
                                                                    'meter_number' => strtolower($request->meter_number)
                                                                ];
                                                                $customer_name = $adm->$check_now($sending_data);
                                                                if ((empty($customer_name)) && ($request->bypass == false || $request->bypass == 'false')) {
                                                                    return response()->json([
                                                                        'status' => 'fail',
                                                                        'message' => 'Invalid Meter Number'
                                                                    ])->setStatusCode(403);
                                                                } else {
                                                                    if (DB::table('user')->where(['id' => $user->id])->update(['bal' => $debit])) {
                                                                        DB::commit();
                                                                        $trans_history = [
                                                                            'username' => $user->username,
                                                                            'amount' => $total_amount,
                                                                            'message' => 'Transaction on process ' . strtoupper($bill_plan->disco_name) . ' ' . strtoupper($request->meter_type) . ' ₦' . $request->amount . ' to ' . $request->meter_number,
                                                                            'oldbal' => $user->bal,
                                                                            'newbal' => $debit,
                                                                            'adex_date' => $this->system_date(),
                                                                            'plan_status' => 0,
                                                                            'transid' => $transid,
                                                                            'role' => 'bill'
                                                                        ];
                                                                        $bill_trans = [
                                                                            'username' => $user->username,
                                                                            'amount' => $request->amount,
                                                                            'disco_name' => $bill_plan->disco_name,
                                                                            'meter_number' => $request->meter_number,
                                                                            'meter_type' => strtoupper($request->meter_type),
                                                                            'charges' => $charges,
                                                                            'newbal' => $debit,
                                                                            'oldbal' => $user->bal,
                                                                            'customer_name' => $customer_name,
                                                                            'system' => $system,
                                                                            'plan_status' => 0,
                                                                            'plan_date' => $this->system_date(),
                                                                            'transid' => $transid
                                                                        ];
                                                                        if ($this->inserting_data('message', $trans_history) && $this->inserting_data('bill', $bill_trans)) {
                                                                            $billvend = new BillSend();
                                                                            $bill_data = [
                                                                                'username' => $user->username,
                                                                                'plan_id' => $request->disco,
                                                                                'transid' => $transid
                                                                            ];
                                                                            $response = $billvend->$check_now($bill_data);
                                                                            if (!empty($response)) {
                                                                                if ($response == 'success') {
                                                                                    DB::table('message')->where(['username' => $user->username, 'transid' => $transid])->update(['plan_status' => 1, 'message' => 'Transaction successful ' . strtoupper($bill_plan->disco_name) . ' ' . strtoupper($request->meter_type) . ' ₦' . $request->amount . ' to ' . $request->meter_number]);
                                                                                    DB::table('bill')->where(['username' => $user->username, 'transid' => $transid])->update(['plan_status' => 1]);
                                                                                    $adex_forgot = DB::table('bill')->where('transid', $transid)->first();
                                                                                    return response()->json([
                                                                                        'disco_name' => strtoupper($bill_plan->disco_name),
                                                                                        'request-id' => $transid,
                                                                                        'amount' => $request->amount,
                                                                                        'charges' => $charges,
                                                                                        'transid' => $transid,
                                                                                        'status' => 'success',
                                                                                        'message' => 'Transaction  successful ' . strtoupper($bill_plan->disco_name) . ' ' . strtoupper($request->meter_type) . ' ₦' . $request->amount . ' to ' . $request->meter_number,
                                                                                        'meter_number' => $request->meter_number,
                                                                                        'meter_type' => strtoupper($request->meter_type),
                                                                                        'oldbal' => $user->bal,
                                                                                        'newbal' => $debit,
                                                                                        'system' => $system,
                                                                                        'token' => $adex_forgot->token,
                                                                                        'wallet_vending' => 'wallet',
                                                                                    ]);
                                                                                } else if ($response == 'proccess') {
                                                                                    return response()->json([
                                                                                        'disco_name' => strtoupper($bill_plan->disco_name),
                                                                                        'request-id' => $transid,
                                                                                        'amount' => $request->amount,
                                                                                        'charges' => $charges,
                                                                                        'status' => 'process',
                                                                                        'message' => 'Transaction on process ' . strtoupper($bill_plan->disco_name) . ' ' . strtoupper($request->meter_type) . ' ₦' . $request->amount . ' to ' . $request->meter_number,
                                                                                        'meter_number' => $request->meter_number,
                                                                                        'meter_type' => strtoupper($request->meter_type),
                                                                                        'oldbal' => $user->bal,
                                                                                        'newbal' => $debit,
                                                                                        'system' => $system,
                                                                                        'wallet_vending' => 'wallet',
                                                                                    ]);
                                                                                } else if ($response == 'fail') {
                                                                                    DB::table('user')->where(['username' => $user->username, 'id' => $user->id])->update(['bal' => $refund]);
                                                                                    DB::table('message')->where(['username' => $user->username, 'transid' => $transid])->update(['plan_status' => 2, 'newbal' => $refund, 'message' => 'Transaction fail ' . strtoupper($bill_plan->disco_name) . ' ' . strtoupper($request->meter_type) . ' ₦' . $request->amount . ' to ' . $request->meter_number]);
                                                                                    DB::table('bill')->where(['username' => $user->username, 'transid' => $transid])->update(['plan_status' => 2, 'newbal' => $refund]);
                                                                                    return response()->json([
                                                                                        'disco_name' => strtoupper($bill_plan->disco_name),
                                                                                        'request-id' => $transid,
                                                                                        'amount' => $request->amount,
                                                                                        'charges' => $charges,
                                                                                        'status' => 'fail',
                                                                                        'message' => 'Transaction  fail ' . strtoupper($bill_plan->disco_name) . ' ' . strtoupper($request->meter_type) . ' ₦' . $request->amount . ' to ' . $request->meter_number,
                                                                                        'meter_number' => $request->meter_number,
                                                                                        'meter_type' => strtoupper($request->meter_type),
                                                                                        'oldbal' => $user->bal,
                                                                                        'newbal' => $refund,
                                                                                        'system' => $system,
                                                                                        'wallet_vending' => 'wallet',
                                                                                    ]);
                                                                                } else {
                                                                                    return response()->json([
                                                                                        'disco_name' => strtoupper($bill_plan->disco_name),
                                                                                        'request-id' => $transid,
                                                                                        'amount' => $request->amount,
                                                                                        'charges' => $charges,
                                                                                        'status' => 'process',
                                                                                        'message' => 'Transaction on process ' . strtoupper($bill_plan->disco_name) . ' ' . strtoupper($request->meter_type) . ' ₦' . $request->amount . ' to ' . $request->meter_number,
                                                                                        'meter_number' => $request->meter_number,
                                                                                        'meter_type' => strtoupper($request->meter_type),
                                                                                        'oldbal' => $user->bal,
                                                                                        'newbal' => $debit,
                                                                                        'system' => $system,
                                                                                        'wallet_vending' => 'wallet',
                                                                                    ]);
                                                                                }
                                                                            } else {
                                                                                return response()->json([
                                                                                    'disco_name' => strtoupper($bill_plan->disco_name),
                                                                                    'request-id' => $transid,
                                                                                    'amount' => $request->amount,
                                                                                    'charges' => $charges,
                                                                                    'status' => 'process',
                                                                                    'message' => 'Transaction on process ' . strtoupper($bill_plan->disco_name) . ' ' . strtoupper($request->meter_type) . ' ₦' . $request->amount . ' to ' . $request->meter_number,
                                                                                    'meter_number' => $request->meter_number,
                                                                                    'meter_type' => strtoupper($request->meter_type),
                                                                                    'oldbal' => $user->bal,
                                                                                    'newbal' => $debit,
                                                                                    'system' => $system,
                                                                                    'wallet_vending' => 'wallet',
                                                                                ]);
                                                                            }
                                                                        } else {
                                                                            DB::table('user')->where(['username' => $user->username, 'id' => $user->id])->update(['bal' => $refund]);
                                                                            DB::table('message')->where('transid', $transid)->delete();
                                                                            DB::table('bill')->where('transid', $transid)->delete();
                                                                            return response()->json([
                                                                                'status' => 'fail',
                                                                                'message' => 'Unable to insert'
                                                                            ])->setStatusCode(403);
                                                                        }
                                                                    } else {
                                                                        return response()->json([
                                                                            'status' => 'fail',
                                                                            'message' => 'unable to debit user'
                                                                        ])->setStatusCode(403);
                                                                    }
                                                                }
                                                            } else {
                                                                return response()->json([
                                                                    'status' => 'fail',
                                                                    'message' => 'Insufficient Account Kindly Fund Your Wallet => ₦' . number_format($user->bal, 2)
                                                                ])->setStatusCode(403);
                                                            }
                                                        } else {
                                                            return response()->json([
                                                                'status' => 'fail',
                                                                'message' => 'Minimum Electricity Purchase for this account is => ₦' . number_format($bill_d->bill_min, 2)
                                                            ])->setStatusCode(403);
                                                        }
                                                    } else {
                                                        return response()->json([
                                                            'status' => 'fail',
                                                            'message' => 'Maximum Electricity Purchase for this account is => ₦' . number_format($bill_d->bill_max, 2)
                                                        ])->setStatusCode(403);
                                                    }
                                                } else {
                                                    return response()->json([
                                                        'status' => 'fail',
                                                        'message' => 'Invalid Amount'
                                                    ])->setStatusCode(403);
                                                }
                                            } else {
                                                return response()->json([
                                                    'status' => 'fail',
                                                    'message' => 'Insufficient Account Kindly Fund Your Wallet => ₦' . number_format($user->bal, 2)
                                                ])->setStatusCode(403);
                                            }
                                        } else {
                                            return response()->json([
                                                'status' => 'fail',
                                                'message' => 'Amount Not Detected'
                                            ])->setStatusCode(403);
                                        }
                                    } else {
                                        return response()->json([
                                            'status' => 'fail',
                                            'message' => 'You have Reach Daily Transaction Limit Kindly Message the Admin To Upgrade Your Account '
                                        ])->setStatusCode(403);
                                    }
                                } else {
                                    return response()->json([
                                        'status' => 'fail',
                                        'message' => 'Electricity Bill Not Available Right Now'
                                    ])->setStatusCode(403);
                                }
                            } else {
                                return response()->json([
                                    'status' => 'fail',
                                    'message' => 'Invalid Disco ID'
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
                'message' => 'Authorization Header Token Required'
            ])->setStatusCode(403);
        }
    }
}
