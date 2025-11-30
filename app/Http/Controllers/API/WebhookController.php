<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    public function Simserver(Request $request)
    {
        if ($request->status and $request->user_reference and $request->true_response) {
            if (DB::table('data')->where(['transid' => $request->status])->count() == 1) {
                $trans = DB::table('data')->where(['transid' => $request->user_reference])->first();
                $user = DB::table('user')->where(['username' => $trans->username, 'status' => 1])->first();
                if ($request->status == 'Done') {
                    $status = 'success';
                    DB::table('data')->where(['transid' => $trans->transid])->update(['plan_status' => 1, 'api_response' => $request->true_response]);
                    DB::table('message')->where(['transid' => $trans->transid])->update(['plan_status' => 1, 'message' => $request->true_response]);
                } else {
                    if ($trans->plan_status !== 2) {

                        if (strtolower($trans->wallet) == 'wallet') {
                            DB::table('user')->where('username', $trans->username)->update(['bal' => $user->bal + $trans->amount]);
                            $user_balance = $user->bal;
                        } else {
                            $wallet_bal = strtolower($trans->wallet) . "_bal";
                            $b = DB::table('wallet_funding')->where(['username' => $trans->username])->first();
                            $user_balance = $b->$wallet_bal;
                            DB::table('wallet_funding')->where('username', $trans->username)->update([$wallet_bal => $user_balance + $trans->amount]);
                        }



                        $status = "fail";
                        DB::table('data')->where(['transid' => $trans->transid])->update(['plan_status' => 2, 'api_response' => $request->true_response, 'oldbal' => $user_balance, 'newbal' => $user_balance + $trans->amount]);
                        DB::table('message')->where(['transid' => $trans->transid])->update(['plan_status' => 2, 'message' => $request->true_response, 'oldbal' => $user_balance, 'newbal' => $user_balance + $trans->amount]);
                    }
                }
                if ($status) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $user->webhook);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['status' => $status, 'request-id' => $trans->transid, 'response' => $request->true_response]));  //Post Fields
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_exec($ch);
                    curl_close($ch);
                }
            }
        } else {
            return ['status' => 'fail'];
        }
    }
    public function AdexWebhook()
    {
        $response = json_decode(file_get_contents("php://input"), true);
        if ((isset($response['status'])) and (isset($response['request-id'])) and isset($response['response'])) {

            if (DB::table('data')->where(['transid' => $response['request-id']])->count() == 1) {
                $trans = DB::table('data')->where(['transid' => $response['request-id']])->first();
                $user = DB::table('user')->where(['username' => $trans->username, 'status' => 1])->first();

                if ($response['status'] == 'success') {
                    $status = "success";
                    DB::table('data')->where(['transid' => $trans->transid])->update(['plan_status' => 1, 'api_response' => $response['response']]);
                    DB::table('message')->where(['transid' => $trans->transid])->update(['plan_status' => 1, 'message' => $response['response']]);
                } else {
                    if ($trans->plan_status !== 2) {
                        $status = "fail";

                        if (strtolower($trans->wallet) == 'wallet') {
                            $user_balance = $user->bal;
                            DB::table('user')->where('username', $trans->username)->update(['bal' => $user->bal + $trans->amount]);
                        } else {
                            $wallet_bal = strtolower($trans->wallet) . "_bal";
                            $b = DB::table('wallet_funding')->where(['username' => $trans->username])->first();
                            $user_balance = $b->$wallet_bal;
                            DB::table('wallet_funding')->where('username', $trans->username)->update([$wallet_bal => $user_balance + $trans->amount]);
                        }


                        DB::table('data')->where(['transid' => $trans->transid])->update(['plan_status' => 2, 'api_response' => $response['response'], 'oldbal' => $user_balance, 'newbal' => $user_balance + $trans->amount]);
                        DB::table('message')->where(['transid' => $trans->transid])->update(['plan_status' => 2, 'message' => $response['response'], 'oldbal' => $user_balance, 'newbal' => $user_balance + $trans->amount]);
                    }
                }
                if ($status) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $user->webhook);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['status' => $status, 'request-id' => $trans->transid, 'response' => $response['response']]));  //Post Fields
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_exec($ch);
                    curl_close($ch);
                }
            }
        }
    }

    public function MegasubWebhook()
    {
        $response = json_decode(file_get_contents("php://input"), true);
        if ($response['status'] and $response['id'] and $response['msg']) {
            if (DB::table('data')->where(['mega_trans' => $response['id']])->where(function ($query) {
                $query->where('plan_status', 1)->orwhere('plan_status', 0);
            })->count() == 1) {
                $trans = DB::table('data')->where(['mega_trans' => $response['id']])->first();
                $user = DB::table('user')->where(['username' => $trans->username, 'status' => 1])->first();
                if ($response['status'] == 'success') {
                    $status = "success";
                    DB::table('data')->where(['transid' => $trans->transid])->update(['plan_status' => 1, 'api_response' => $response['msg']]);
                    DB::table('message')->where(['transid' => $trans->transid])->update(['plan_status' => 1, 'message' => $response['msg']]);
                } else {
                    if ($trans->plan_status !== 2) {
                        if (strtolower($trans->wallet) == 'wallet') {
                            DB::table('user')->where('username', $trans->username)->update(['bal' => $user->bal + $trans->amount]);
                            $user_balance = $user->bal;
                        } else {
                            $wallet_bal = strtolower($trans->wallet) . "_bal";
                            $b = DB::table('wallet_funding')->where(['username' => $trans->username])->first();
                            $user_balance = $b->$wallet_bal;
                            DB::table('wallet_funding')->where('username', $trans->username)->update([$wallet_bal => $user_balance + $trans->amount]);
                        }
                        $status = "fail";
                        DB::table('data')->where(['transid' => $trans->transid])->update(['plan_status' => 2, 'api_response' => $response['msg'], 'oldbal' => $user_balance, 'newbal' => $user_balance + $trans->amount]);
                        DB::table('message')->where(['transid' => $trans->transid])->update(['plan_status' => 2, 'message' => $response['msg'], 'oldbal' => $user_balance, 'newbal' => $user_balance + $trans->amount]);
                    }
                }
                if ($status) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $user->webhook);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['status' => $status, 'request-id' => $trans->transid, 'response' => $response['msg']]));  //Post Fields
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_exec($ch);
                    curl_close($ch);
                }
            }
        }
    }
}
