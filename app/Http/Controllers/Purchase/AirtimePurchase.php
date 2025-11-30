<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class  AirtimePurchase extends Controller
{

    public function BuyAirtime(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            $validator = Validator::make($request->all(), [
                'network' => 'required',
                'phone' => 'required|numeric|digits:11',
                'bypass' => 'required',
                'plan_type' => 'required',
                'amount' => 'required|numeric|integer|not_in:0|gt:0'
            ], [
                'network.required' => 'Network Id Required',
                'phone.required' => 'Phone Number Required',
                'phone.digits' => 'Phone Number Digits Must Be 11',
            ]);
            $system = env('APP_NAME');
            $transid = $this->purchase_ref('AIRTIME_');
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
                'network' => 'required',
                'phone' => 'required|numeric|digits:11',
                'bypass' => 'required',
                'plan_type' => 'required',
                'amount' => 'required|numeric|integer|not_in:0|gt:0',
                'user_id' => 'required'
            ]);
            $system = "APP";
            $transid = $this->purchase_ref('AIRTIME_');
            
            // Debug logging for mobile app airtime purchase
            \Log::info('🚨 AIRTIME PURCHASE DEBUG - Request received:', [
                'user_id' => $request->user_id,
                'pin' => $request->pin,
                'authorization' => $request->header('Authorization'),
                'device_key' => env('ADEX_DEVICE_KEY')
            ]);
            
            if (DB::table('user')->where(['id' => $this->verifyapptoken($request->user_id), 'status' => 1])->count() == 1) {
                $d_token = DB::table('user')->where(['id' => $this->verifyapptoken($request->user_id), 'status' => 1])->first();
                
                \Log::info('🚨 AIRTIME PURCHASE DEBUG - User found:', [
                    'user_id' => $d_token->id,
                    'username' => $d_token->username,
                    'stored_pin' => $d_token->pin,
                    'sent_pin' => $request->pin,
                    'pin_match' => ($d_token->pin == $request->pin)
                ]);
                
                $accessToken = $d_token->apikey;
                \Log::info('🚨 AIRTIME PURCHASE DEBUG - User authentication successful');
            } else {
                \Log::error('🚨 AIRTIME PURCHASE DEBUG - User not found:', [
                    'user_id' => $request->user_id,
                    'verified_user_id' => $this->verifyapptoken($request->user_id)
                ]);
                $accessToken = 'null';
            }
        } else {
            // api verification
            $validator = Validator::make($request->all(), [
                'network' => 'required',
                'phone' => 'required|numeric|digits:11',
                'bypass' => 'required',
                'plan_type' => 'required',
                'amount' => 'required|numeric|integer|not_in:0|gt:0',
                'request-id' => 'required|unique:airtime,transid'
            ]);
            $system = "API";
            $id = "request-id";
            $transid = $request->$id;
            $d_token = $request->header('Authorization');
            $accessToken = trim(str_replace("Token", "", $d_token));
        }
        // carry out transaction
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 'fail'
            ])->setStatusCode(403);
        }
        if ($accessToken) {
            $user = DB::table('user')->where(['apikey' => $accessToken, 'status' => 1])->sharedLock()->first();
            if ($user) {
                if (DB::table('block')->where(['number' => $request->phone])->count() == 0) {
                    // declear user type
                    if ($user->type == 'SMART') {
                        $user_type = 'smart';
                    } else if ($user->type == 'AGENT') {
                        $user_type = 'agent';
                    } else if ($user->type == 'AWUF') {
                        $user_type = 'awuf';
                    } else if ($user->type == 'API') {
                        $user_type = 'api';
                    } else {
                        $user_type = 'special';
                    }
                        if (DB::table('airtime')->where('transid', $transid)->count() == 0 and DB::table('message')->where('transid', $transid)->count() == 0) {
                            // declare all variable
                            $network = $request->network;
                            $phone = $request->phone;
                            if ($request->bypass == true || $request->bypass == 'true') {
                                $bypass = true;
                            } else {
                                $bypass = false;
                            }
                            $plan_type = strtolower($request->plan_type);
                            $amount = $request->amount;

                            // check if network exits before
                            if (DB::table('network')->where('plan_id', $network)->count() == 1) {
                                //network details
                                $network_d = DB::table('network')->where('plan_id', $network)->first();

                                if ($plan_type == 'vtu' || $plan_type == 'sns') {
                                    // lock services
                                    if ($plan_type == 'vtu') {
                                        $adex_lock = "network_vtu";
                                    } else {
                                        $adex_lock = 'network_share';
                                    }

                                    // check number
                                    if ($bypass == false || $request->bypass == 'false') {
                                        $validate = substr($phone, 0, 4);
                                        if ($network_d->network == "MTN") {
                                            if (strpos(" 0702 0703 0713 0704 0706 0707 0716 0802 0803 0806 0810 0813 0814 0816 0903 0913 0906 0916 0804 ", $validate) == FALSE || strlen($phone) != 11) {
                                                return response()->json([
                                                    'status' => 'fail',
                                                    'message' => 'This is not a MTN Number => ' . $phone
                                                ])->setStatusCode(403);
                                            } else {
                                                $adex_bypass = true;
                                            }
                                        } else if ($network_d->network == "GLO") {
                                            if (strpos(" 0805 0705 0905 0807 0907 0707 0817 0917 0717 0715 0815 0915 0811 0711 0911 ", $validate) == FALSE || strlen($phone) != 11) {
                                                return response()->json([
                                                    'status' => 'fail',
                                                    'message' => 'This is not a GLO Number =>' . $phone
                                                ])->setStatusCode(403);
                                            } else {
                                                $adex_bypass = true;
                                            }
                                        } else if ($network_d->network == "AIRTEL") {
                                            if (strpos(" 0904 0802 0902 0702 0808 0911 0908 0708 0918 0818 0718 0812 0912 0712 0801 0701 0901 0907 0917 ", $validate) == FALSE || strlen($phone) != 11) {
                                                return response()->json([
                                                    'status' => 'fail',
                                                    'message' => 'This is not a AIRTEL Number => ' . $phone
                                                ])->setStatusCode(403);
                                            } else {
                                                $adex_bypass = true;
                                            }
                                        } else if ($network_d->network == "9MOBILE") {
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
                                            })->whereDate('adex_date',  Carbon::now("Africa/Lagos"))->sum('amount');

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

                                                if ($plan_type == 'sns') {
                                                    $type = 'share';
                                                } else {
                                                    $type = $plan_type;
                                                }
                                                if ($network_d->network == '9MOBILE') {
                                                    $real_network = 'mobile';
                                                } else {
                                                    $real_network = $network_d->network;
                                                }
                                                $check_for_me = strtolower($real_network) . "_" . strtolower($type) . "_" . strtolower($user_type);
                                                $discount = DB::table('airtime_discount')->first();
                                                DB::beginTransaction();
                                                $user = DB::table('user')->where(['id' => $user->id])->lockForUpdate()->first();
                                                if ($network_d->$adex_lock == 1) {
                                                    if (is_numeric($user->bal)) {
                                                        if ($amount > 0) {
                                                            $discount_amount = ($request->amount / 100) * $discount->$check_for_me;
                                                            // maximmum
                                                            if ($discount->max_airtime >= $amount) {
                                                                if ($amount >= $discount->min_airtime) {
                                                                    if ($user->bal >= $discount_amount) {
                                                                        $debit = $user->bal - $discount_amount;
                                                                        $refund = $debit + $discount_amount;
                                                                        $trans_history = [
                                                                            'username' => $user->username,
                                                                            'amount' => $amount,
                                                                            'message' => 'Transaction on process ' . $network_d->network . ' ' . strtoupper($plan_type) . ' to ' . $phone,
                                                                            'oldbal' => $user->bal,
                                                                            'newbal' => $debit,
                                                                            'adex_date' => $this->system_date(),
                                                                            'plan_status' => 0,
                                                                            'transid' => $transid,
                                                                            'role' => 'airtime'
                                                                        ];
                                                                        $airtime_history = [
                                                                            'username' => $user->username,
                                                                            'network' => $network_d->network,
                                                                            'network_type' => strtoupper($plan_type),
                                                                            'amount' => $amount,
                                                                            'oldbal' => $user->bal,
                                                                            'newbal' => $debit,
                                                                            'discount' => $discount_amount,
                                                                            'transid' => $transid,
                                                                            'plan_date' => $this->system_date(),
                                                                            'plan_status' => 0,
                                                                            'plan_phone' => $phone,
                                                                            'system' => $system
                                                                        ];
                                                                        if (DB::table('user')->where(['id' => $user->id])->update(['bal' => $debit])) {
                                                                        DB::commit();
                                                                            if ($this->inserting_data('message', $trans_history) and $this->inserting_data('airtime', $airtime_history)) {
                                                                                // purchase data now
                                                                                $sending_data = [
                                                                                    'transid' => $transid,
                                                                                    'username' => $user->username
                                                                                ];
                                                                                $adexm = new AirtimeSend();
                                                                                $airtime_sel = DB::table('airtime_sel')->first();
                                                                                $vending = strtolower($real_network) . "_" . strtolower($type);
                                                                                $check_now = $airtime_sel->$vending;
                                                                                $response = $adexm->$check_now($sending_data);
                                                                                if (!empty($response)) {
                                                                                    if ($response == 'success') {
                                                                                        // state success transaction
                                                                                        DB::table('message')->where(['username' => $user->username, 'transid' => $transid])->update(['plan_status' => 1, 'message' => 'successfully purchase ' . $network_d->network . ' ' . strtoupper($type) . ' to ' . $phone . ' , ₦' . $amount]);
                                                                                        DB::table('airtime')->where(['username' => $user->username, 'transid' => $transid])->update(['plan_status' => 1]);
                                                                                        return response()->json([
                                                                                            'network' => $network_d->network,
                                                                                            'request-id' => $transid,
                                                                                            'amount' => $amount,
                                                                                            'transid' => $transid,
                                                                                            'discount' => $discount_amount,
                                                                                            'status' => 'success',
                                                                                            'message' => 'successfully purchase ' . $network_d->network . ' ' . strtoupper($type) . ' to ' . $phone . ' , ₦' . $amount,
                                                                                            'phone_number' => $phone,
                                                                                            'oldbal' => $user->bal,
                                                                                            'newbal' => $debit,
                                                                                            'system' => $system,
                                                                                            'plan_type' => strtoupper($plan_type),
                                                                                            'wallet_vending' => "wallet"
                                                                                        ]);
                                                                                    } else if ($response == 'process') {
                                                                                        return response()->json([
                                                                                            'network' => $network_d->network,
                                                                                            'request-id' => $transid,
                                                                                            'amount' => $amount,
                                                                                            'discount' => $discount_amount,
                                                                                            'status' => 'process',
                                                                                            'message' => 'Transaction on process ' . $network_d->network . ' ' . strtoupper($type) . ' to ' . $phone,
                                                                                            'phone_number' => $phone,
                                                                                            'oldbal' => $user->bal,
                                                                                            'newbal' => $debit,
                                                                                            'system' => $system,
                                                                                            'wallet_vending' => 'wallet',
                                                                                            'plan_type' => strtoupper($plan_type),
                                                                                        ]);
                                                                                    } else if ($response == 'fail') {
                                                                                         $check_fail = DB::table('airtime')->where(['username' => $user->username, 'transid' => $transid])->first();
                                                                                          if ($check_fail->plan_status != 2) {
                                                                                        $admin_refund = DB::table('user')->where(['id' => $user->id])->first();
                                                                                        DB::table('user')->where(['username' => $user->username, 'id' => $user->id])->update(['bal' =>  $admin_refund->bal + $discount_amount]);
                                                                                        DB::table('message')->where(['username' => $user->username, 'transid' => $transid])->update(['plan_status' => 2, 'message' => 'Transaction fail ' . $network_d->network . ' ' . strtoupper($type) . ' to ' . $phone . ' , ₦' . $amount, 'newbal' => $refund]);
                                                                                        DB::table('airtime')->where(['username' => $user->username, 'transid' => $transid])->update(['plan_status' => 2, 'newbal' => $refund]);
                                                                                          }
                                                                                        return response()->json([
                                                                                            'network' => $network_d->network,
                                                                                            'request-id' => $transid,
                                                                                            'amount' => $amount,
                                                                                            'discount' => $discount_amount,
                                                                                            'status' => 'fail',
                                                                                            'message' => 'Transaction fail ' . $network_d->network . ' ' . strtoupper($type) . ' to ' . $phone . ' , ₦' . $amount,
                                                                                            'phone_number' => $phone,
                                                                                            'oldbal' => $user->bal,
                                                                                            'newbal' => $refund,
                                                                                            'system' => $system,
                                                                                            'plan_type' => strtoupper($plan_type),
                                                                                            'wallet_vending' => "wallet"
                                                                                        ]);
                                                                                    } else {
                                                                                        return response()->json([
                                                                                            'network' => $network_d->network,
                                                                                            'request-id' => $transid,
                                                                                            'amount' => $amount,
                                                                                            'discount' => $discount_amount,
                                                                                            'status' => 'process',
                                                                                            'message' => 'Transaction on process ' . $network_d->network . ' ' . strtoupper($type) . ' to ' . $phone,
                                                                                            'phone_number' => $phone,
                                                                                            'oldbal' => $user->bal,
                                                                                            'newbal' => $debit,
                                                                                            'system' => $system,
                                                                                            'wallet_vending' => 'wallet',
                                                                                            'plan_type' => strtoupper($plan_type),
                                                                                        ]);
                                                                                    }
                                                                                } else {
                                                                                    return response()->json([
                                                                                        'network' => $network_d->network,
                                                                                        'request-id' => $transid,
                                                                                        'amount' => $amount,
                                                                                        'discount' => $discount_amount,
                                                                                        'status' => 'process',
                                                                                        'message' => 'Transaction on process ' . $network_d->network . ' ' . strtoupper($type) . ' to ' . $phone,
                                                                                        'phone_number' => $phone,
                                                                                        'oldbal' => $user->bal,
                                                                                        'newbal' => $debit,
                                                                                        'system' => $system,
                                                                                        'wallet_vending' => 'wallet',
                                                                                        'plan_type' => strtoupper($plan_type),
                                                                                    ]);
                                                                                }
                                                                            } else {
                                                                                // refund user here
                                                                                DB::table('message')->where('transid', $transid)->delete();
                                                                                DB::table('airtime')->where('transid', $transid)->delete();
                                                                                DB::table('user')->where(['username' => $user->username, 'id' => $user->id])->update(['bal' => $refund]);
                                                                                return response()->json([
                                                                                    'status' => 'fail',
                                                                                    'message' => 'kindly re try after some mins'
                                                                                ])->setStatusCode(403);
                                                                            }
                                                                        } else {
                                                                            return response()->json([
                                                                                'status' => 'fail',
                                                                                'message' => 'Unable to debit user right now'
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
                                                                        'message' => 'Minimum Airtime Purchase for this account is => ₦' . number_format($discount->min_airtime, 2)
                                                                    ])->setStatusCode(403);
                                                                }
                                                            
                                                            } else {
                                                                return response()->json([
                                                                    'status' => 'fail',
                                                                    'message' => 'Maximum Airtime Purchase for this account is => ₦' . number_format($discount->max_airtime, 2)
                                                                ])->setStatusCode(403);
                                                            }
                                                        } else {
                                                            return response()->json([
                                                                'status' => 'fail',
                                                                'message' => 'invalid amount'
                                                            ])->setStatusCode(403);
                                                        }
                                                    } else {
                                                        return response()->json([
                                                            'status' => 'fail',
                                                            'message' => 'Unknown Account Balance'
                                                        ])->setStatusCode(403);
                                                    }
                                                } else {
                                                    return response()->json([
                                                        'status' => 'fail',
                                                        'message' => $network_d->network . ' ' . strtoupper($plan_type) . ' is not available right now'
                                                    ])->setStatusCode(403);
                                                }
                                            } else {
                                                return response()->json([
                                                    'status' => 'fail',
                                                    'message' => 'You have Reach Daily Transaction Limit Kindly Message the Admin To Upgrade Your Account'
                                                ])->setStatusCode(403);
                                            }
                                        } else {
                                            return response()->json([
                                                'status' => 'fail',
                                                'message' => 'Phone Number Bypass Failed'
                                            ])->setStatusCode(403);
                                        }
                                    } else {
                                        return response()->json([
                                            'status' => 'fail',
                                            'message' => 'Invalid Phone Number => ' . $phone
                                        ])->setStatusCode(403);
                                    }
                                } else {
                                    return response()->json([
                                        'status' => 'fail',
                                        'message' => 'Invalid Network Plan Type'
                                    ])->setStatusCode(403);
                                }
                            } else {
                                return response()->json([
                                    'status' => 'fail',
                                    'message' => 'Invalid Network ID'
                                ])->setStatusCode(403);
                            }
                        } else {
                            return response()->json([
                                'status' => 'fail',
                                'message' => 'Transaction Plan Id Exits'
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
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Authorization Header Token Required'
            ])->setStatusCode(403);
        }
    }
}