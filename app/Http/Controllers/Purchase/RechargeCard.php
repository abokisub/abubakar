<?php

namespace App\Http\Controllers\Purchase;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class  RechargeCard extends Controller
{

    public function RechargeCardPurchase(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        $validator = Validator::make($request->all(), [
            'network' => 'required',
            'quantity' => 'required|numeric|integer|not_in:0|gt:0|min:1|max:100',
            'card_name' => 'required|max:200',
            'plan_type' => 'required',
        ]);
        $transid = $this->purchase_ref('Recharge_card_');
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
            // api verification
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

        if (!empty($accessToken)) {
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 'fail'
                ])->setStatusCode(403);
            } else {
                $user_check = DB::table('user')->where(['apikey' => $accessToken, 'status' => 1]);
                if ($user_check->count() == 1) {
                    $user = $user_check->first();
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
                    if (DB::table('network')->where('plan_id', $request->network)->count() == 1) {
                        $network = DB::table('network')->where('plan_id', $request->network)->first();
                        if ($network->recharge_card == 1) {
                            if (DB::table('recharge_card_plan')->where(['network' => $network->network, 'plan_id' => $request->plan_type, 'plan_status' => 1])->count() == 1) {
                                // user limit
                                $recharge_card_plan = DB::table('recharge_card_plan')->where(['network' => $network->network, 'plan_id' => $request->plan_type])->first();
                                $all_limit = DB::table('message')->where(['username' => $user->username])->where(function ($query) {
                                    $query->where('role', '!=', 'credit');
                                    $query->where('role', '!=', 'debit');
                                    $query->where('role', '!=', 'upgrade');
                                    $query->where('plan_status', '!=', 2);
                                })->where('adex_date', '>=', Carbon::now())->sum('amount');
                                if ($this->core()->allow_limit == 1 && $user->kyc == 0) {
                                    if ($all_limit + $recharge_card_plan->$user_type <= $user->user_limit) {
                                        $adex_new_go = true;
                                    } else {
                                        $adex_new_go = false;
                                    }
                                } else {
                                    $adex_new_go = true;
                                }
                                if ($adex_new_go == true) {
                                    $recharge_card_price = $recharge_card_plan->$user_type * $request->quantity;
                                    if (DB::table('recharge_card')->where('transid', $transid)->count() == 0 && DB::table('message')->where('transid', $transid)->count() == 0) {
                                        DB::beginTransaction();
                                        $user = DB::table('user')->where(['id' => $user->id])->lockForUpdate()->first();
                                        if ($user->bal > 0) {
                                            if ($user->bal >= $recharge_card_price) {
                                                $debit = $user->bal - $recharge_card_price;
                                                $refund = $debit + $recharge_card_price;
                                                if (DB::table('user')->where(['id' => $user->id])->update(['bal' => $debit])) {
                                                    DB::commit();
                                                    $trans_history = [
                                                        'username' => $user->username,
                                                        'amount' => $recharge_card_price,
                                                        'message' =>  $network->network . ' Recharge Card Printing On Process Quantity is ' . $request->quantity,
                                                        'oldbal' => $user->bal,
                                                        'newbal' => $debit,
                                                        'adex_date' => $this->system_date(),
                                                        'plan_status' => 0,
                                                        'transid' => $transid,
                                                        'role' => 'recharge_card'
                                                    ];

                                                    $recharge_card_trans = [
                                                        'username' => $user->username,
                                                        'network' => $network->network,
                                                        'plan_name' => $recharge_card_plan->name,
                                                        'load_pin' => $recharge_card_plan->load_pin,
                                                        'amount' => $recharge_card_price,
                                                        'plan_date' => $this->system_date(),
                                                        'transid' => $transid,
                                                        'oldbal' => $user->bal,
                                                        'newbal' => $debit,
                                                        'plan_status' => 0,
                                                        'system' => $system,
                                                        'quantity' => $request->quantity,
                                                        'card_name' => $request->card_name,

                                                        'check_balance' => $recharge_card_plan->check_balance
                                                    ];
                                                    if (DB::table('recharge_card')->insert($recharge_card_trans) and DB::table('message')->insert($trans_history)) {
                                                        $sending_data = [
                                                            'purchase_plan' => $recharge_card_plan->plan_id,
                                                            'transid' => $transid,
                                                            'username' => $user->username
                                                        ];
                                                        if ($network->network == '9MOBILE') {
                                                            $vending = 'mobile';
                                                        } else {
                                                            $vending = strtolower($network->network);
                                                        }
                                                        $adexm = new RechargeCardSend();
                                                        $data_sel = DB::table('recharge_card_sel')->first();
                                                        $check_now = $data_sel->$vending;
                                                        $response = $adexm->$check_now($sending_data);
                                                        if ($response) {
                                                            if ($response == 'success') {
                                                                // get the pin and serial number here
                                                                $stock_pin = DB::table('dump_recharge_card_pin')->where(['network' => $network->network, 'username' => $user->username, 'transid' => $transid])->get();
                                                                $sold_pin = null;
                                                                $sold_serial = null;
                                                                foreach ($stock_pin as $real_pin) {
                                                                    $sold_pin[] = $real_pin->pin;
                                                                    $sold_serial[] = $real_pin->serial;
                                                                }
                                                                DB::table('message')->where(['username' => $user->username, 'transid' => $transid])->update(['plan_status' => 1,  'message' =>  $network->network . ' Recharge Card Printing Successful']);
                                                                DB::table('recharge_card')->where(['username' => $user->username, 'transid' => $transid])->update(['plan_status' => 1]);
                                                                return response()->json([
                                                                    'network' => $network->network,
                                                                    'request-id' => $transid,
                                                                    'transid' => $transid,
                                                                    'amount' => $recharge_card_price,
                                                                    'quantity' => $request->quantity,
                                                                    'status' => 'success',
                                                                    'message' =>  $network->network . ' Recharge Card Printing Successful',
                                                                    'card_name' => $request->card_name,
                                                                    'oldbal' => $user->bal,
                                                                    'newbal' => $debit,
                                                                    'system' => $system,
                                                                    'serial' => implode(',', $sold_serial),
                                                                    'pin' => implode(',', $sold_pin),
                                                                    'load_pin' => $recharge_card_plan->load_pin,
                                                                    'check_balance' => $recharge_card_plan->check_balance
                                                                ]);
                                                            } else {
                                                                // transaction fail
                                                                DB::table('user')->where('id', $user->id)->update(['bal' => $refund]);
                                                                // trans history
                                                                DB::table('message')->where(['username' => $user->username, 'transid' => $transid])->update(['plan_status' => 2, 'oldbal' => $user->bal, 'newbal' => $refund, 'message' => 'Recharge Card Transaction  fail ' . $network->network]);
                                                                DB::table('recharge_card')->where(['username' => $user->username, 'transid' => $transid])->update(['plan_status' => 2, 'oldbal' => $user->bal, 'newbal' => $refund]);
                                                                return response()->json([
                                                                    'network' => $network->network,
                                                                    'request-id' => $transid,
                                                                    'amount' => $recharge_card_price,
                                                                    'quantity' => $request->quantity,
                                                                    'status' => 'fail',
                                                                    'message' =>  $network->network . ' Recharge Card Printing Fail ',
                                                                    'card_name' => $request->card_name,
                                                                    'oldbal' => $user->bal,
                                                                    'newbal' => $debit,
                                                                    'system' => $system,
                                                                ]);
                                                            }
                                                        } else {
                                                            return response()->json([
                                                                'network' => $network->network,
                                                                'request-id' => $transid,
                                                                'amount' => $recharge_card_price,
                                                                'quantity' => $request->quantity,
                                                                'status' => 'process',
                                                                'message' =>  $network->network . ' Recharge Card Printing On Process Quantity is ' . $request->quantity,
                                                                'card_name' => $request->card_name,
                                                                'oldbal' => $user->bal,
                                                                'newbal' => $debit,
                                                                'system' => $system,
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
                                            'message' => 'please try again later'
                                        ]);
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
                                    'message' => 'Invalid ' . $network->network . ' Recharge Card Plan Type'
                                ])->setStatusCode(403);
                            }
                        } else {
                            return response()->json([
                                'status' => 'fail',
                                'message' => $network->network . 'Recharge Card Not Avalaible Now'
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
                        'message' => 'Invalid Authorization Token'
                    ])->setStatusCode(403);
                }
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Authorization Access Token Required'
            ])->setStatusCode(403);
        }
    }
}
