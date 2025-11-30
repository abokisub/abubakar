<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class  MessageController extends Controller
{
    public function Gmail(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    $general = $this->general();
                    $adex_search = ['{username}', '{email}', '{fullname}', '{phone}', '{webhook}', '{apikey}', '{address}', '{ref}', '{type}', '{wema}', '{rolex}', '{ster}', '{fed}', '{otp}', '{user_limit}', '{bal}', '{rebal}'];
                    if ($request->status == 'ALL') {
                        $all_user = DB::table('user')->get();
                    } else if ($request->status == 'CUSTOM') {
                        $all_user = DB::table('user')->where('username', $request->user_username)->get();
                    } else {
                        $all_user = DB::table('user')->where('type', $request->status)->get();
                    }
                    foreach ($all_user as $user) {
                        $change_adex = [$user->username, $user->email, $user->name, $user->phone, $user->webhook, $user->apikey, $user->address, $user->ref, $user->type, $user->wema, $user->rolex, $user->sterlen, $user->fed, $user->otp, $user->user_limit, '₦' . number_format($user->bal, 2), '₦' . number_format($user->refbal, 2)];
                        $real_message = str_replace($adex_search, $change_adex, $request->message);
                        $email_data = [
                            'name' => $user->name,
                            'email' => $user->email,
                            'username' => $user->username,
                            'title' => $request->title,
                            'sender_mail' => $general->app_email,
                            'messages' => $real_message,
                            'app_name' => env('APP_NAME'),
                        ];
                        MailController::send_mail($email_data, 'email.notif');
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'User Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 403,
                    'message' => 'Not Authorised'
                ])->setStatusCode(403);
            }
        } else {
            return redirect(env('ERROR_500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function System(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    $adex_search = ['{username}', '{email}', '{fullname}', '{phone}', '{webhook}', '{apikey}', '{address}', '{ref}', '{type}', '{wema}', '{rolex}', '{ster}', '{fed}', '{otp}', '{user_limit}', '{bal}', '{rebal}'];
                    if ($request->status == 'ALL') {
                        $all_user = DB::table('user')->get();
                    } else if ($request->status == 'CUSTOM') {
                        $all_user = DB::table('user')->where('username', $request->user_username)->get();
                    } else {
                        $all_user = DB::table('user')->where('type', $request->status)->get();
                    }
                    foreach ($all_user as $user) {
                        $change_adex = [$user->username, $user->email, $user->name, $user->phone, $user->webhook, $user->apikey, $user->address, $user->ref, $user->type, $user->wema, $user->rolex, $user->sterlen, $user->fed, $user->otp, $user->user_limit, '₦' . number_format($user->bal, 2), '₦' . number_format($user->refbal, 2)];
                        $real_message = str_replace($adex_search, $change_adex, $request->message);

                        DB::table('notif')->insert(['username' => $user->username, 'message' => $real_message, 'date' => $this->system_date(), 'adex' => 0]);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'User Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 403,
                    'message' => 'Not Authorised'
                ])->setStatusCode(403);
            }
        } else {
            return redirect(env('ERROR_500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function Bulksms(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    $adex_search = ['{username}', '{email}', '{fullname}', '{phone}', '{webhook}', '{apikey}', '{address}', '{ref}', '{type}', '{wema}', '{rolex}', '{ster}', '{fed}', '{otp}', '{user_limit}', '{bal}', '{rebal}'];
                    if ($request->status == 'ALL') {
                        $all_user = DB::table('user')->get();
                    } else if ($request->status == 'CUSTOM') {
                        $all_user = DB::table('user')->where('username', $request->user_username)->get();
                    } else {
                    }
                    foreach ($all_user as $user) {
                        $change_adex = [$user->username, $user->email, $user->name, $user->phone, $user->webhook, $user->apikey, $user->address, $user->ref, $user->type, $user->wema, $user->rolex, $user->sterlen, $user->fed, $user->otp, $user->user_limit, '₦' . number_format($user->bal, 2), '₦' . number_format($user->refbal, 2)];
                        $real_message = str_replace($adex_search, $change_adex, $request->message);
                        $adex_api = DB::table('other_api')->first();
                        $r = array(
                            "user" => $adex_api->hollatag_username,
                            "pass" => $adex_api->hollatag_password,
                            "from" => env('APP_NAME'),
                            "to" => $user->phone,
                            "msg" => $real_message,
                            "type" => 0,
                        );

                        $url = 'https://sms.hollatags.com/api/send/';
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POSTFIELDS,  http_build_query($r));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_exec($ch);
                        curl_close($ch);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'User Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 403,
                    'message' => 'Not Authorised'
                ])->setStatusCode(403);
            }
        } else {
            return redirect(env('ERROR_500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
}
