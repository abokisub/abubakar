<?php

namespace App\Http\Controllers\APP;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MailController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\FirebaseNotificationService;


class Auth extends Controller
{
    public function AppLogin(Request $request)
    {
        if (env('ADEX_DEVICE_KEY') == $request->header('Authorization')) {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required',
            ], [
                'username.required' => 'Your Username or Phone Number or Email is Required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 403,
                    'message' => $validator->errors()->first()
                ])->setStatusCode(403);
            } else {
                $check_system =  User::where(function($query) use ($request){
                    $query->orWhere('username', $request->username)
                          ->orWhere('phone', $request->username)
                          ->orWhere('email', $request->username);
                });
                if ($check_system->count() == 1) {
                    $user = $check_system->get()[0];
                    $this->monnify_account($user->username);
                    $this->insert_stock($user->username);
                    $user = DB::table('user')->where(['id' => $user->id])->first();
                    $user_details = [
                        'username' => $user->username,
                        'name'  => $user->name,
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'bal' => number_format($user->bal, 2),
                        'refbal' => number_format($user->refbal, 2),
                        'kyc' => $user->kyc,
                        'type' => $user->type,
                        'pin' => $user->pin,
                        'profile_image' => $user->profile_image,
                        'sterlen' => $user->sterlen,
                        'fed' => $user->fed,
                        'wema' => $user->wema,
                        'rolex' => $user->rolex,
                        'address' => $user->address,
                        'webhook' => $user->webhook,
                        'about' => $user->about,
                        'apikey' => $user->apikey,
                        'notif' => DB::table('notif')->where(['username' => $user->username, 'adex' => 0])->count(),
                    ];
                    $hash = substr(sha1(md5($request->password)), 3, 10);
                    $mdpass = md5($request->password);

                    if($user->pin == $request->password && $request->adex_check == 'adex_check_me_nd_olu'){
                        if (isset($request->app_token)) {
                            DB::table('user')->where(['id' => $user->id])->update(['app_token' => $request->app_token]);
                        }
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Login successfully',
                            'user' => $user_details,
                            'token' => $this->generateapptoken($user->id)
                        ]); 
                    }

                    if ((password_verify($request->password, $user->password)) xor ($request->password == $user->password) xor ($hash == $user->password) xor ($mdpass == $user->password)) {
                        if ($user->status == 1) {
                            if (isset($request->app_token)) {
                                DB::table('user')->where(['id' => $user->id])->update(['app_token' => $request->app_token]);
                            }
                            if ($user->app_token) {
                                try {
                                    $firebase = new FirebaseNotificationService();
                                    $firebase->sendToToken(
                                        $user->app_token,
                                        "Login Notification.",
                                        "Welcome back, $user->username! You have successfully logged in."
                                    );
                                } catch (\Exception $e) {
                                    \Log::warning('Firebase notification failed: ' . $e->getMessage());
                                }
                            }
                            return response()->json([
                                'status' => 'success',
                                'message' => 'Login successfully',
                                'user' => $user_details,
                                'token' => $this->generateapptoken($user->id)
                            ]);
                        } else if ($user->status == 2) {
                            return response()->json([
                                'status' => 403,
                                'message' => $user->username . ' Your Account Has Been Banned'
                            ])->setStatusCode(403);
                        } else if ($user->status == 3) {
                            return response()->json([
                                'status' => 403,
                                'message' => $user->username . ' Your Account Has Been Deactivated'
                            ])->setStatusCode(403);
                        } else if ($user->status == 0) {
                            return response()->json([
                                'status' => 'unverify',
                                'message' => $user->username . ' Your Account Not Yet verified',
                                'user' => $user_details,
                                'token' => $this->generateapptoken($user->id),
                            ]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'System is unable to verify user'
                            ])->setStatusCode(403);
                        }
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => $request->adex_check == 'adex_check_me_nd_olu' ? 'Incorrect Transaction Pin' : 'Invalid Password Note Password is Case Sensitive'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Invalid Username and Password'
                    ])->setStatusCode(403);
                }
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(403);
        }
    }
    public function AppVerify(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
            $validator = Validator::make($request->all(), [
                'otp' => 'required|string',
                'app_key' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 403,
                    'message' => $validator->errors()->first()
                ])->setStatusCode(403);
            } else {
                if (DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 0])->count() == 1) {
                    $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 0])->first();
                    if ($user->otp == $request->otp) {
                        DB::table('user')->where(['id' => $user->id])->update(['otp' => null, 'status' => 1]);
                        
                        if($user->pin != null){
                        $email_data = [
                            'name' => $user->name,
                            'email' => $user->email,
                            'username' => $user->username,
                            'title' => 'WELCOME EMAIL',
                            'sender_mail' => $this->general()->app_email,
                            'system_email' => $this->general()->app_email,
                            'app_name' => $this->general()->app_name,
                            'pin' => $user->pin,
                        ];
                        MailController::send_mail($email_data, 'email.welcome');
                        }
                        return response()->json([
                            'status' => 'success',
                            'message' => 'account verify'
                        ]);
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Invalid OTP'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'status' => 505,
                        'message' => 'Account Expired'
                    ])->setStatusCode(505);
                }
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(403);
        }
    }
    
    public function DeleteUserAccountNot(Request $request){
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
            $validator = Validator::make($request->all(), [
                'app_key' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 403,
                    'message' => $validator->errors()->first()
                ])->setStatusCode(403);
            } else {
                if (DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 1])->count() == 1) {
        
           $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 1])->first();

                    $general = $this->general();
                    foreach(DB::table('user')->where('status', 1)->where(function($query){
                        $query->orWhere('type', 'ADMIN')->orWhere('type', 'CUSTOMER');
                    })->get() as $admin_user){
                         $email_data = [
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'email' => $admin_user->email,
                        'user_email' => $user->email,
                        'username' => $user->username,
                        'balance' => number_format($user->bal,2),
                        'title' => strtoupper($admin_user->username) . ', ' . strtoupper($user->name) . ' want to delete his account',
                        'sender_mail' => $general->app_email,
                        'app_name' => env('APP_NAME'),
                        'type' => $user->type,
                        'admin_username' => $admin_user->username
                      
                    ];
                    MailController::send_mail($email_data, 'email.delete_account'); 
                    }
                  
                    return response()->json([
                        'status' => 'status',
                        'message' => 'sent'
                    ]);
                } else {
                    return response()->json([
                        'message' => 'expired'
                    ])->setStatusCode(505);
                }
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(403);
        }
    }
    public function ResendOtp(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
            $validator = Validator::make($request->all(), [
                'app_key' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 403,
                    'message' => $validator->errors()->first()
                ])->setStatusCode(403);
            } else {
                if (DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 0])->count() == 1) {


                    $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 0])->first();

                    $general = $this->general();
                    $otp = random_int(1000, 9999);
                    $data = [
                        'otp' => $otp
                    ];
                    $tableid = [
                        'id' => $user->id
                    ];
                    $this->updateData($data, 'user', $tableid);
                    $email_data = [
                        'name' => $user->name,
                        'email' => $user->email,
                        'username' => $user->username,
                        'title' => 'Account Verification',
                        'sender_mail' => $general->app_email,
                        'app_name' => env('APP_NAME'),
                        'otp' => $otp
                    ];
                    MailController::send_mail($email_data, 'email.verify');
                    return response()->json([
                        'status' => 'status',
                        'message' => 'New OTP Resent to Your Email'
                    ]);
                } else {
                    return response()->json([
                        'message' => 'expired'
                    ])->setStatusCode(505);
                }
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(403);
        }
    }

    public function Signup(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
            $validator = validator::make($request->all(), [
                'name' => 'required|max:199|min:8',
                'email' => 'required|unique:user,email|max:255|email',
                'phone' => 'required|numeric|unique:user,phone|digits:11',
                'password' => 'required|min:8',
                'username' => 'required|unique:user,username|max:12|string|alpha_num',
                // 'pin' => 'required|numeric|digits:4'
            ], [
                'name.required' => 'Full Name is Required',
                'email.required' => 'E-mail is Required',
                'phone.required' => 'Phone Number Required',
                'password.required' => 'Password Required',
                'username.required' => 'Username Required',
                'username.unique' => 'Username already Taken',
                'phone.unique' => 'Phone Number already Taken',
                'username.max' => 'Username Maximum Length is 12 ' . $request->username,

                'email.unique' => 'Email Alreay Taken',
                'password.min' => 'Password Not Strong Enough',
                'name.min' => 'Invalid Full Name',
                'name.max' => 'Invalid Full Name',
                'phone.numeric' => 'Phone Number Must be Numeric ' . $request->phone,

                'pin.required' => 'Transaction Pin Required',
                'pin.numeric' => 'Transaction Pin Numeric',
                'pin.digits' => 'Transaction Pin Digits Must Be 4'
            ]);
            // checking referal user details
            if ($request->ref != null) {
                $check_ref = DB::table('user')
                    ->where('username', '=', $request->ref)
                    ->count();
            }
            if ($validator->fails()) {

                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 403
                ])->setStatusCode(403);
            } else if (substr($request->phone, 0, 1) != '0') {
                return response()->json([
                    'message' => 'Invalid Phone Number',
                    'status' => 403
                ])->setStatusCode(403);
            } else
    if ($request->ref != null && $check_ref == 0) {
                return response()->json([
                    'message' => 'Invalid Referral Username You can Leave the referral Box Empty',
                    'status' => '403'
                ])->setStatusCode(403);
    }else if($request->pin != null && strlen((string)$request->pin) != 4){
            return response()->json([
                    'message' => 'Transaction Pin Must Be 4 Digits',
                    'status' => '403'
                ])->setStatusCode(403);
    
            } else {
                $user = new User();
                $user->name = $request->name;
                $user->username = $request->username;
                $user->email = $request->email;
                $user->phone = $request->phone;
                $user->password = password_hash($request->password,  PASSWORD_DEFAULT, array('cost' => 16));
                // $user->password = Hash::make($request->password);
                $user->apikey =  bin2hex(openssl_random_pseudo_bytes(30));
                $user->bal = '0.00';
                $user->refbal = '0.00';
                $user->ref = $request->ref;
                $user->type = 'SMART';
                $user->date = Carbon::now("Africa/Lagos")->toDateTimeString();
                $user->kyc = '0';
                $user->status = '0';
                $user->user_limit = $this->adex_key()->default_limit;
                $user->pin = $request->pin;
                $user->save();
                if ($user != null) {
                    $this->monnify_account($user->username);
                    $this->insert_stock($user->username);
                    $user = DB::table('user')->where(['id' => $user->id])->first();
                    $user_details = [
                        'username' => $user->username,
                        'name'  => $user->name,
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'bal' => number_format($user->bal, 2),
                        'refbal' => number_format($user->refbal, 2),
                        'kyc' => $user->kyc,
                        'type' => $user->type,
                        'pin' => $user->pin,
                        'profile_image' => $user->profile_image,
                        'sterlen' => $user->sterlen,
                        'fed' => $user->fed,
                        'wema' => $user->wema,
                        'rolex' => $user->rolex,
                        'address' => $user->address,
                        'webhook' => $user->webhook,
                        'about' => $user->about,
                        'apikey' => $user->apikey,
                        
                        'notif' => DB::table('notif')->where(['username' => $user->username, 'adex' => 0])->count(),
                       
                        
                    ];
                    $token = $this->generateapptoken($user->id);
                    $use = $this->core();
                    $general = $this->general();
                    if ($use != null) {
                        if ($use->is_verify_email) {
                            $otp = random_int(1024, 9999);
                            $data = [
                                'otp' => $otp
                            ];
                            $tableid = [
                                'username' => $user->username
                            ];
                            $this->updateData($data, 'user', $tableid);
                            $email_data = [
                                'name' => $user->name,
                                'email' => $user->email,
                                'username' => $user->username,
                                'pin' => $user->pin,
                                'title' => 'Account Verification',
                                'sender_mail' => $general->app_email,
                                'app_name' => env('APP_NAME'),
                                'otp' => $otp
                            ];
                            MailController::send_mail($email_data, 'email.verify');
                            return response()->json([
                                'status' => 'unverify',
                                'username' => $user->username,
                                'token' => $token,
                                'user' =>  $user_details
                            ]);
                        } else {
                            $data = [
                                'status' => 1
                            ];
                            $tableid = [
                                'username' => $user->username
                            ];
                            $this->updateData($data, 'user', $tableid);
                            if($request->pin != null){
                            $email_data = [
                                'name' => $user->name,
                                'email' => $user->email,
                                'username' => $user->username,
                                'pin' => $user->pin,
                                'title' => 'WELCOME EMAIL',
                                'sender_mail' => $general->app_email,
                                'system_email' => $general->app_email,
                                'app_name' => $general->app_name
                            ];
                            MailController::send_mail($email_data, 'email.welcome');
                            }
                               if (isset($request->app_token)) {
                                DB::table('user')->where(['id' => $user->id])->update(['app_token' => $request->app_token]);
                            }
                            return response()->json([
                                'status' => 'success',
                                'username' => $user->username,
                                'token' => $token,
                                'user' =>  $user_details
                            ]);
                        }
                    } else {
                        $data = [
                            'status' => 1,
                        ];
                        $tableid = [
                            'username' => $user->username
                        ];
                        $this->updateData($data, 'user', $tableid);
                        $email_data = [
                            'name' => $user->name,
                            'email' => $user->email,
                            'username' => $user->username,
                            'title' => 'WELCOME EMAIL',
                            'sender_mail' => $general->app_email,
                            'system_email' => $general->app_email,
                            'app_name' => $general->app_name
                        ];
                        MailController::send_mail($email_data, 'email.welcome');
                        return response()->json([
                            'status' => 'success',
                            'username' => $user->username,
                            'token' => $token,
                            'user' =>  $user_details
                        ]);
                    }
                } else {
                    return response()->json(
                        [
                            'status' => 403,
                            'message' => 'Unable to Register User Please Try Again Later',
                        ]
                    )->setStatusCode(403);
                }
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(403);
        }
    }
    public function FingerPrint(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
            $validator = Validator::make($request->all(), [
                'app_key' => 'required',
                'password' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 403,
                    'message' => $validator->errors()->first()
                ])->setStatusCode(403);
            } else {
                if (DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 1])->count() == 1) {
                    $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 1])->first();
                    $this->monnify_account($user->username);
                    $this->insert_stock($user->username);
                    $user = DB::table('user')->where(['id' => $user->id])->first();
                    $user_details = [
                        'username' => $user->username,
                        'name'  => $user->name,
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'bal' => number_format($user->bal, 2),
                        'refbal' => number_format($user->refbal, 2),
                        'kyc' => $user->kyc,
                        'type' => $user->type,
                        'pin' => $user->pin,
                        'profile_image' => $user->profile_image,
                        'sterlen' => $user->sterlen,
                        'fed' => $user->fed,
                        'wema' => $user->wema,
                        'rolex' => $user->rolex,
                        'address' => $user->address,
                        'webhook' => $user->webhook,
                        'about' => $user->about,
                        'apikey' => $user->apikey,
                        
                        'notif' => DB::table('notif')->where(['username' => $user->username, 'adex' => 0])->count(),
                       
                        
                    ];
                    $hash = substr(sha1(md5($request->password)), 3, 10);
                    $mdpass = md5($request->password);
                    if ((password_verify($request->password, $user->password)) xor ($request->password == $user->password) xor ($hash == $user->password) xor ($mdpass == $user->password)) {
                        //  if(Hash::check($request->password, $user->password)){
                        
                             if (isset($request->app_token)) {
                                DB::table('user')->where(['id' => $user->id])->update(['app_token' => $request->app_token]);
                            }
                        if ($user->status == 1) {
                            //here we go .....
                            $this->monnify_account($user->username);
                            $this->insert_stock($user->username);
                            return response()->json([
                                'status' => 'success',
                                'message' => 'Login successfully',
                                'user' => $user_details,
                                'token' => $user->app_key
                            ]);
                        } else if ($user->status == 2) {
                            return response()->json([
                                'status' => 403,
                                'message' => $user->username . ' Your Account Has Been Banned'
                            ])->setStatusCode(403);
                        } else if ($user->status == 3) {
                            return response()->json([
                                'status' => 403,
                                'message' => $user->username . ' Your Account Has Been Deactivated'
                            ])->setStatusCode(403);
                        } else if ($user->status == 0) {
                            return response()->json([
                                'status' => 'unverify',
                                'message' => $user->username . ' Your Account Not Yet verified',
                                'user' => $user_details,
                                'token' => $this->generateapptoken($user->id),
                            ]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'System is unable to verify user'
                            ])->setStatusCode(403);
                        }
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Invalid Password Note Password is Case Sensitive'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'message' => 'expired'
                    ])->setStatusCode(505);
                }
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(403);
        }
    }
    public function APPLOAD(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
            $validator = Validator::make($request->all(), [
                'app_key' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 403,
                    'message' => $validator->errors()->first()
                ])->setStatusCode(505);
            } else {
                if (DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 1])->count() == 1) {
                    $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 1])->first();
                    $this->monnify_account($user->username);
                    $this->insert_stock($user->username);
                    $user = DB::table('user')->where(['id' => $user->id])->first();
                    $user_details = [
                        'username' => $user->username,
                        'name'  => $user->name,
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'bal' => number_format($user->bal, 2),
                        'refbal' => number_format($user->refbal, 2),
                        'kyc' => $user->kyc,
                        'type' => $user->type,
                        'pin' => $user->pin,
                        'profile_image' => $user->profile_image,
                        'sterlen' => $user->sterlen,
                        'fed' => $user->fed,
                        'wema' => $user->wema,
                        'rolex' => $user->rolex,
                        'address' => $user->address,
                        'webhook' => $user->webhook,
                        'about' => $user->about,
                        'apikey' => $user->apikey,
                        
                        'notif' => DB::table('notif')->where(['username' => $user->username, 'adex' => 0])->count(),
                       
                         
                    ];
                    $data_purchase = DB::table('data')->where(['username' => $user->username, 'plan_status' => 1])->whereDate('plan_date', Carbon::now())->get();
                    $total_gb = 0;
                    $gb = 0;
                    $calculate_gb = '0GB';
                    foreach ($data_purchase as $data) {
                        $plans = $data->plan_name;
                        $check_gb = substr($plans, -2);
                        if ($check_gb  == 'MB') {
                            $mb = rtrim($plans, "MB");
                            $gb = $mb / 1024;
                        } elseif ($check_gb == 'GB') {
                            $gb = rtrim($plans, "GB");
                        } elseif ($check_gb == 'TB') {
                            $tb = rtrim($plans, 'TB');
                            $gb = ceil($tb * 1020);
                        }
                        $total_gb += $gb;
                    }
                    if ($total_gb >= 1024) {
                        $calculate_gb = $total_gb / 1024 . 'TB';
                    } else {
                        $calculate_gb =  $total_gb . 'GB';
                    }

                    return response()->json([
                        'status' => 'success',
                        'referral_count' => DB::table('user')->where(['ref' => $user->username])->count(),
                        'user' => $user_details,
                        'data_purchased' => $calculate_gb,
                        'setting' => DB::table('settings')->first(),
                        'notif' => DB::table('notif')->where(['username' => $user->username, 'adex' => 0])->count()
                    ]);
                } else {
                    return response()->json([
                        'message' => 'APP Server Down',
                    ])->setStatusCode(505);
                }
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }
    }
    public function AppGeneral(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {

            return response()->json([
                'general' => $this->general(),
                'setting' => $this->core(),
                'adex_key' => DB::table('adex_key')->select('mon_con_num', 'mon_app_key', 'bank_name', 'account_number', 'account_name')->first(),
            ]);
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }
    }
    public function APPMOnify(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
            if (DB::table('user')->where(['id' => $this->verifyapptoken($request->user_id), 'status' => 1])->count() == 1) {
                if (DB::table('deposit')->where(['monify_ref' => $request->referrence_id])->count() == 0) {
                    $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->user_id), 'status' => 1])->first();
                    $sender = "https://api.monnify.com/api/v2/transactions/" . urlencode($request->referrence_id);
                    $adex_key = DB::table('adex_key')->first();
                    $base_monnify = base64_encode($adex_key->mon_app_key . ':' . $adex_key->mon_sk_key);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://api.monnify.com/api/v1/auth/login');
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt(
                        $ch,
                        CURLOPT_HTTPHEADER,
                        [
                            "Authorization: Basic " . $base_monnify,
                        ]
                    );
                    $json = curl_exec($ch);
                    curl_close($ch);
                    $result = json_decode($json, true);
                    if (isset($result['responseBody']['accessToken'])) {
                        $accessToken = $result['responseBody']['accessToken'];
                    } else {
                        $accessToken = null;
                    }
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL =>  $sender,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => array(
                            "Authorization: Bearer " . $accessToken,
                            "Content-Type: application/json"
                        ),
                    ));
                    $res = curl_exec($curl);
                    $response = json_decode($res, true);
                    if (isset($response)) {
                        if (isset($response['responseBody']['paymentStatus'])) {
                            $amount_paid = $response["responseBody"]["amountPaid"];
                            $charges = ($amount_paid / 100) * $this->core()->monnify_charge;
                            $transid = $this->purchase_ref('APP_FUNDING_');
                            $credit = $amount_paid - $charges;
                            DB::table('deposit')->insert([
                                'username' => $user->username,
                                'amount' => $amount_paid,
                                'oldbal' => $user->bal,
                                'newbal' => $user->bal,
                                'wallet_type' => 'User Wallet',
                                'type' => 'AutoMated Bank Transfer (APP)',
                                'credit_by' => 'Monnify Automated Bank Transfer (APP)',
                                'date' => $this->system_date(),
                                'status' => 0,
                                'transid' => $transid,
                                'charges' => $charges,
                                'monify_ref' => $request->referrence_id
                            ]);
                            $trans_status = $response['responseBody']['paymentStatus'];
                            if (strtolower($trans_status) == 'paid') {
                                DB::table('deposit')->where(['monify_ref' => $request->referrence_id, 'status' => 0])->update(['status' => 1, 'oldbal' => $user->bal, 'newbal' => $user->bal + $credit]);
                                DB::table('user')->where(['id' => $user->id])->update(['bal' => $user->bal + $credit]);
                                DB::table('notif')->insert([
                                    'username' => $user->username,
                                    'message' => 'Account Credited By Monnify ATM (APP) ₦' . number_format($credit, 2),
                                    'date' => $this->system_date(),
                                    'adex' => 0
                                ]);
                                // app notification
                                $data = [
                                    "to" => $user->app_token,
                                    "priority" => "high",
                                    "notification" => [
                                        "title" => env('APP_NAME'),
                                        "body" => 'Account Has Been Credited By Monnify ATM (APP) ₦' . number_format($credit, 2),
                                    ]
                                ];
                                $dataString = json_encode($data);
                                $headers = [
                                    'Authorization: key=' . env('FIRE_BASE_KEY'),
                                    'Content-Type: application/json',
                                ];
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                                curl_setopt($ch, CURLOPT_POST, true);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
                                curl_exec($ch);
                            }
                        } else {
                            return response()->json([
                                'message' => 'payment not initialised'
                            ])->setStatusCode(403);
                        }
                    } else {
                        return response()->json([
                            'message' => 'payment not initialised'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'message' => 'transaction id exit'
                    ])->setStatatusCode(403);
                }
            } else {
                return response()->json([
                    'message' => 'invalid User ID'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }
    }
    public function ManualFunding(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
            if (DB::table('user')->where(['id' => $this->verifyapptoken($request->id), 'status' => 1])->count() == 1) {
                $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->id), 'status' => 1])->first();
                $validator = Validator::make($request->all(), [
                    'bank_name' => 'required',
                    'bank_code' => 'required|numeric',
                    'account_number' => 'required|digits:10|numeric',
                    'amount' => 'required|numeric|not_in:0|gt:0'
                ], [
                    'account_number.digits' => 'Your Account Number Must Be 10 Digits',

                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 403,
                        'message' => $validator->errors()->first()
                    ])->setStatusCode(403);
                } else {
                    $send_request = "https://api.monnify.com/api/v1/disbursements/account/validate?accountNumber=$request->account_number&bankCode=$request->bank_code";
                    $json_response = json_decode(@file_get_contents($send_request), true);
                    if (!empty($json_response)) {
                        if ($json_response['requestSuccessful'] == true) {
                            $transid = $this->purchase_ref('Bank_');
                            $data_bank = [
                                'account_number' => $request->account_number,
                                'bank_name' => $request->bank_name,
                                'bank_code' => $request->bank_code,
                                'account_name' => $json_response['responseBody']['accountName'],
                                'amount' => $request->amount,
                                'date' => $this->system_date(),
                                'plan_status' => 0,
                                'username' => $user->username,
                                'transid' => $transid
                            ];
                            DB::table('bank_transfer')->insert($data_bank);
                            $admins = DB::table('user')->where(['status' => 1])->where(function ($query) {
                                $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                            })->get();
                            foreach ($admins as $admin) {
                                $email_data = [
                                    'email' => $admin->email,
                                    'username' => $user->username,
                                    'title' => 'Manual Bank Transfer',
                                    'sender_mail' => $this->general()->app_email,
                                    'app_name' => $this->general()->app_name,
                                    'mes' => $user->username . " Transferred  ₦" . number_format($request->amount, 2) . " to your bank account. Reference is => " . $transid
                                ];
                                MailController::send_mail($email_data, 'email.purchase');
                            }

                            DB::table('request')->insert(['username' => $user->username, 'message' => $user->username . " Transferred  ₦" . number_format($request->amount, 2) . " to your bank account. Reference is => " . $transid, 'date' => $this->system_date(), 'transid' => $transid, 'status' => 0, 'title' => 'MANUAL BANK TRANSFER']);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Inavlid Account Details'
                            ])->setStatusCode(403);
                        }
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Inavlid Account Details'
                        ])->setStatusCode(403);
                    }
                }
            } else {
                return response()->json([
                    'message' => 'Kindly Logout The Account And Try Again',
                ])->setStatusCode(505);
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }
    }
    public function Network(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
            if ($request->type == 'data') {
                return response()->json([
                    'data' => DB::table('network')->where(function ($query) {
                        $query->orWhere('network_cg', 1)->orWhere('network_sme', 1)->orWhere('network_g',  1);
                    })->select('id', 'network', 'plan_id', 'network_sme', 'network_cg', 'network_g')->get()
                ]);
            } else if ($request->type == 'airtime') {
                 $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->token? $request->token : '00'), 'status' => 1]);
                        if ($user->count() == 1) {
                            $adex = $user->first();
                            if ($adex->type == 'SMART') {
                                $user_type = strtolower($adex->type);
                            } else if ($adex->type == 'AGENT') {
                                $user_type = strtolower($adex->type);
                            } else if ($adex->type == 'AWUF') {
                                $user_type = strtolower($adex->type);
                            } else if ($adex->type == 'API') {
                                $user_type = strtolower($adex->type);
                            } else {
                                $user_type = 'special';
                            }
                            $network_plan = [];
                            foreach(DB::table('network')->where('network_vtu', 1)->get() as $network){
                            if ($network->network == '9MOBILE') {
                                $real_network = 'mobile';
                            } else {
                                $real_network = $network->network;
                            }
                             $check_for_vtu = strtolower($real_network) . "_vtu_" . $user_type;
                            $check_for_sns = strtolower($real_network) . "_share_" . $user_type;
                            $airtime_discount = DB::table('airtime_discount')->first();
                            $vtu_price = $airtime_discount->$check_for_vtu;
                            $share_price = $airtime_discount->$check_for_sns;
                            $network_plan[] = ['id' => $network->id, 'plan_id' => $network->plan_id, 'network_vtu' => $network->network_vtu, 'network_share' => $network->network_share, 'amount' => $vtu_price, 'network' => $network->network];
                            }
                            
                            return response()->json([
                                'data' => $network_plan
                                ]);
                            
                        }else{  
                
                return response()->json([
                    'data' => DB::table('network')->where(function ($query) {
                        $query->orWhere('network_vtu', 1)->orWhere('network_share', 1);
                    })->select('id', 'network', 'plan_id', 'network_vtu', 'network_share')->get()
                ]);
                        }
            } else if ($request->type == 'cash') {
                $network =  DB::table('network')->where('cash', 1)->select('id', 'network', 'plan_id')->get();
                $cash_plan = [];
                $discount = DB::table('cash_discount')->first();
                foreach ($network as $cash) {
                    if ($cash->network == 'MTN') {
                        $cash_plan[] = ['network' => 'MTN', 'plan_id' => $cash->plan_id, 'id' => $cash->id, 'amount' => $discount->mtn, 'number' => $discount->mtn_number];
                    }
                    if ($cash->network == 'AIRTEL') {
                        $cash_plan[] = ['network' => 'AIRTEL', 'plan_id' => $cash->plan_id, 'id' => $cash->id, 'amount' => $discount->airtel, 'number' => $discount->airtel_number];
                    }
                    if ($cash->network == 'GLO') {
                        $cash_plan[] = ['network' => 'GLO', 'plan_id' => $cash->plan_id, 'id' => $cash->id, 'amount' => $discount->glo, 'number' => $discount->glo_number];
                    }
                    if ($cash->network == '9MOBILE') {
                        $cash_plan[] = ['network' => '9MOBILE', 'plan_id' => $cash->plan_id, 'id' => $cash->id, 'amount' => $discount->mobile, 'number' => $discount->mobile_number];
                    }
                }
                return response()->json([
                    'data' => $cash_plan,
                ]);
            }else if($request->type == 'data_card'){
                
                 return response()->json([
                    'data' => DB::table('network')->where('data_card', 1)->select('id', 'network', 'plan_id')->get()
                ]);
                
            }else if($request->type == 'recharge_card'){
                 return response()->json([
                    'data' => DB::table('network')->where('recharge_card', 1)->select('id', 'network', 'plan_id')->get()
                ]);
            
            } else {
                return response()->json([
                    'status' => 403,
                    'message' => 'invalid type'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }
    }

    public function NetworkType(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
            if (!empty($request->id)) {
                $net = DB::table('network')->where(['plan_id' => $request->id]);
                if ($net->count() != 0) {
                    $network = $net->first();
                    if ($request->type == 'data') {
                        $data_plan = [];
                        if ($network->network_sme == 1) {
                            $data_plan[] = ['network' => 'SME', 'plan_id' => '1', 'id' => 1];
                        }
                        if ($network->network_g == 1) {
                            $data_plan[] = ['network' => 'GIFTING', 'plan_id' => '2', 'id' => 2];
                        }
                        if ($network->network_cg == 1) {
                            $data_plan[] = ['network' => 'COOPERATE GIFTING', 'plan_id' => '3', 'id' => 3];
                        }
                        return response()->json([
                            'data' => $data_plan
                        ]);
                    } else if ($request->type == 'airtime') {
                        $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->token), 'status' => 1]);
                        if ($user->count() == 1) {
                            $adex = $user->first();
                            if ($adex->type == 'SMART') {
                                $user_type = strtolower($adex->type);
                            } else if ($adex->type == 'AGENT') {
                                $user_type = strtolower($adex->type);
                            } else if ($adex->type == 'AWUF') {
                                $user_type = strtolower($adex->type);
                            } else if ($adex->type == 'API') {
                                $user_type = strtolower($adex->type);
                            } else {
                                $user_type = 'special';
                            }
                            if ($network->network == '9MOBILE') {
                                $real_network = 'mobile';
                            } else {
                                $real_network = $network->network;
                            }
                            $check_for_vtu = strtolower($real_network) . "_vtu_" . $user_type;
                            $check_for_sns = strtolower($real_network) . "_share_" . $user_type;
                            $airtime_discount = DB::table('airtime_discount')->first();
                            $vtu_price = $airtime_discount->$check_for_vtu;
                            $share_price = $airtime_discount->$check_for_sns;
                        } else {
                            $vtu_price = 0;
                            $share_price = 0;
                        }

                        $airtime_plan = [];
                        if ($network->network_vtu == 1) {
                            $airtime_plan[] = ['network' => 'VTU', 'plan_id' => 'vtu', 'id' => 1, 'amount' => $vtu_price];
                        }
                        if ($network->network_share == 1) {
                            $airtime_plan[] = ['network' => 'SHARE AND SELL', 'plan_id' => 'sns', 'id' => 2, 'amount' => $share_price];
                        }
                        return response()->json([
                            'data' => $airtime_plan
                        ]);
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'invalid type'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'message' => 'Select Network'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'message' => 'Select Network'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }
    }
    public function DataCardPlans(Request $request){
         $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifyapptoken($request->id)]);
                if ($check_user->count() == 1) {
                    $adex = $check_user->first();
                    // validate form
                    $main_validator = validator::make($request->all(), [
                        'network' => 'required',
                        //'network_type' => 'required',
                    ]);
                    // validate user type
                    if ($adex->type == 'SMART') {
                        $user_type = 'smart';
                    } else if ($adex->type == 'AGENT') {
                        $user_type = 'agent';
                    } else if ($adex->type == 'AWUF') {
                        $user_type = 'awuf';
                    } else if ($adex->type == 'API') {
                        $user_type = 'api';
                    } else {
                        $user_type = 'special';
                    }
                    if ($main_validator->fails()) {
                        return response()->json([
                            'message' => $main_validator->errors()->first(),
                            'status' => 403
                        ])->setStatusCode(403);
                    } else {
                        if (DB::table('network')->where('plan_id', $request->network)->count() == 1) {
                            $get_network = DB::table('network')->where('plan_id', $request->network)->first();
                          
                            $all_plan = DB::table('data_card_plan')->where(['network' => $get_network->network,  'plan_status' => 1]);
                            if ($all_plan->count() > 0) {
                                foreach ($all_plan->get() as $adex => $plan) {
                                    $data_plan[] =  ['name' => $plan->name . $plan->plan_size . ' ' . $plan->plan_type . ' = ₦' . number_format($plan->$user_type, 2) . ' ' . $plan->plan_day, 'plan_id' => $plan->plan_id, 'amount' => '₦' . number_format($plan->$user_type, 2), 'id' => $plan->id];
                                }
                            } else {
                                $data_plan = [];
                            }
                            return response()->json([
                                'status' => 'success',
                                'data' => $data_plan,
                            ]);
                        } else {
                            return response()->json([
                                'message' => 'please select network'
                            ])->setStatusCode(403);
                        }
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
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }
                            
    }
     public function RechargeCardPlans(Request $request){
         $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifyapptoken($request->id)]);
                if ($check_user->count() == 1) {
                    $adex = $check_user->first();

                    // validate form
                    $main_validator = validator::make($request->all(), [
                        'network' => 'required',
                        //'network_type' => 'required',
                    ]);
                    // validate user type
                    if ($adex->type == 'SMART') {
                        $user_type = 'smart';
                    } else if ($adex->type == 'AGENT') {
                        $user_type = 'agent';
                    } else if ($adex->type == 'AWUF') {
                        $user_type = 'awuf';
                    } else if ($adex->type == 'API') {
                        $user_type = 'api';
                    } else {
                        $user_type = 'special';
                    }
                    if ($main_validator->fails()) {
                        return response()->json([
                            'message' => $main_validator->errors()->first(),
                            'status' => 403
                        ])->setStatusCode(403);
                    } else {
                        if (DB::table('network')->where('plan_id', $request->network)->count() == 1) {
                            $get_network = DB::table('network')->where('plan_id', $request->network)->first();
                          
                            $all_plan = DB::table('recharge_card_plan')->where(['network' => $get_network->network,  'plan_status' => 1]);
                            if ($all_plan->count() > 0) {
                                foreach ($all_plan->get() as $adex => $plan) {
                                    $data_plan[] =  ['name' => $plan->name .' = ₦' . number_format($plan->$user_type, 2) , 'plan_id' => $plan->plan_id, 'amount' => '₦' . number_format($plan->$user_type, 2), 'id' => $plan->id];
                                }
                            } else {
                                $data_plan = [];
                            }
                            return response()->json([
                                'status' => 'success',
                                'data' => $data_plan,
                            ]);
                        } else {
                            return response()->json([
                                'message' => 'please select network'
                            ])->setStatusCode(403);
                        }
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
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }
                            
    }
    public function DataPlans(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifyapptoken($request->id)]);
                if ($check_user->count() == 1) {
                    $adex = $check_user->first();

                    // validate form
                    $main_validator = validator::make($request->all(), [
                        'network' => 'required',
                        //'network_type' => 'required',
                    ]);
                    // validate user type
                    if ($adex->type == 'SMART') {
                        $user_type = 'smart';
                    } else if ($adex->type == 'AGENT') {
                        $user_type = 'agent';
                    } else if ($adex->type == 'AWUF') {
                        $user_type = 'awuf';
                    } else if ($adex->type == 'API') {
                        $user_type = 'api';
                    } else {
                        $user_type = 'special';
                    }
                    if ($main_validator->fails()) {
                        return response()->json([
                            'message' => $main_validator->errors()->first(),
                            'status' => 403
                        ])->setStatusCode(403);
                    } else {
                        if (DB::table('network')->where('plan_id', $request->network)->count() == 1) {
                            $get_network = DB::table('network')->where('plan_id', $request->network)->first();
                            if(isset($request->network_type)){
                            $all_plan = DB::table('data_plan')->where(['network' => $get_network->network, 'plan_type' => $request->network_type, 'plan_status' => 1]);
                            if ($all_plan->count() > 0) {
                                foreach ($all_plan->get() as $adex => $plan) {
                                    $data_plan[] =  ['name' => $plan->plan_name . $plan->plan_size . ' ' . $plan->plan_type . ' = ₦' . number_format($plan->$user_type, 2) . ' ' . $plan->plan_day, 'plan_id' => $plan->plan_id, 'amount' => '₦' . number_format($plan->$user_type, 2), 'id' => $plan->id];
                                }
                            } else {
                                $data_plan = [];
                            }
                            return response()->json([
                                'status' => 'success',
                                'data' => $data_plan,
                                'network' => $get_network->network,
                                'plan_type' => $request->network_type
                            ]);
                        }else{
                              $sme = [];
                              $cg = [];
                              $gifting = [];
                              
                              foreach(DB::table('data_plan')->where(['network' => $get_network->network, 'plan_type' => 'SME', 'plan_status' => 1])->get() as $adex => $plan){
                                  $sme[] = ['name' => $plan->plan_name . $plan->plan_size . ' ' . $plan->plan_type, 'network' => $plan->network, 'plan_type' => $plan->plan_type, 'plan_day' => $plan->plan_day, 'plan_id' => $plan->plan_id, 'amount' => '₦' . number_format($plan->$user_type, 2), 'id' => $plan->id];
                              }
                               foreach(DB::table('data_plan')->where(['network' => $get_network->network, 'plan_type' => 'GIFTING', 'plan_status' => 1])->get() as $adex => $plan){
                                  $gifting[] = ['name' => $plan->plan_name . $plan->plan_size . ' ' . $plan->plan_type, 'network' => $plan->network, 'plan_type' => $plan->plan_type, 'plan_day' => $plan->plan_day, 'plan_id' => $plan->plan_id, 'amount' => '₦' . number_format($plan->$user_type, 2), 'id' => $plan->id];
                              }
                               foreach(DB::table('data_plan')->where(['network' => $get_network->network, 'plan_type' => 'COOPERATE GIFTING', 'plan_status' => 1])->get() as $adex => $plan){
                                  $cg[] = ['name' => $plan->plan_name . $plan->plan_size . ' ' . $plan->plan_type, 'plan_type' => $plan->plan_type, 'network' => $plan->network, 'plan_day' => $plan->plan_day, 'plan_id' => $plan->plan_id, 'amount' => '₦' . number_format($plan->$user_type, 2), 'id' => $plan->id];
                              }
                              $dresult = [
                                  'sme' => $sme,
                                  'cg' => $cg,
                                  'gifting' => $gifting
                                  ];
                              return response()->json([
                                   'data' => $dresult
                                  ]);
                        }
                        } else {
                            return response()->json([
                                'message' => 'please select network'
                            ])->setStatusCode(403);
                        }
                       
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
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }
    }

    public function TransactionPin(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {

            // validate form
            $main_validator = validator::make($request->all(), [
                'pin' => 'required|numeric|digits:4',
                'user_id' => 'required',
            ]);

            if ($main_validator->fails()) {
                return response()->json([
                    'message' => $main_validator->errors()->first(),
                    'status' => 403
                ])->setStatusCode(403);
            } else {
                if (DB::table('user')->where(['id' => $this->verifyapptoken($request->user_id), 'status' => 1])->count() == 1) {
                    $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->user_id), 'status' => 1])->first();
                    

                    
                    if ($user->pin == $request->pin) {
                        return response()->json([
                            'message' => 'correct',
                            'status' => 'success'
                        ]);
                    } else {
                        return response()->json([
                            'message' => 'Invalid Transaction Pin',
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'message' => 'Account Log Out',
                    ])->setStatusCode(403);
                }
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }
    }
    public function CableBillID(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
            if ($request->type == 'cable') {
                $cable_plan = [];
                $cable_lock = DB::table('cable_result_lock')->first();
                $cable_price = DB::table('cable_charge')->first();
                $cable_plan_id = DB::table('cable_id')->get();
                foreach ($cable_plan_id as $id) {
                    if ($id->cable_name == 'DSTV') {
                        if ($cable_lock->dstv == 1) {
                            $cable_plan[] = ['network' => 'DSTV', 'plan_id' => $id->plan_id, 'id' => $id->id, 'amount' => $cable_price->dstv, 'number' => $cable_price->direct];
                        }
                    }
                    if ($id->cable_name == 'GOTV') {
                        if ($cable_lock->gotv == 1) {
                            $cable_plan[] = ['network' => 'GOTV', 'plan_id' => $id->plan_id, 'id' => $id->id, 'amount' => $cable_price->gotv, 'number' => $cable_price->direct];
                        }
                    }

                    if ($id->cable_name == 'STARTIME') {
                        if ($cable_lock->startime == 1) {
                            $cable_plan[] = ['network' => 'STARTIME', 'plan_id' => $id->plan_id, 'id' => $id->id, 'amount' => $cable_price->startime, 'number' => $cable_price->direct];
                        }
                    }
                }
                return response()->json([
                    'data' => $cable_plan
                ]);
            } else if ($request->type == 'bill') {
                $bill_plan = [];
                $bill_id = DB::table('bill_plan')->where('plan_status', 1)->get();
                $bill_price = DB::table('bill_charge')->first();
                foreach ($bill_id as $id) {

                    $bill_plan[] = ['network' => $id->disco_name, 'plan_id' => $id->plan_id, 'id' => $id->id, 'number' => $bill_price->direct, 'amount' => $bill_price->bill];
                }
                return response()->json([
                    'data' => $bill_plan
                ]);
            } else if ($request->type == 'exam') {
                $exam_plan = [];
                $exam_id = DB::table('exam_id')->get();
                $exam_lock = DB::table('cable_result_lock')->first();
                $exam_price = DB::table('result_charge')->first();

                foreach ($exam_id as $id) {
                    if ($id->exam_name == 'WAEC') {
                        if ($exam_lock->waec == 1) {
                            $exam_plan[] = ['network' => $id->exam_name, 'plan_id' => $id->plan_id, 'amount' => $exam_price->waec, 'id' => $id->id];
                        }
                    }

                    if ($id->exam_name == 'NECO') {
                        if ($exam_lock->neco == 1) {
                            $exam_plan[] = ['network' => $id->exam_name, 'plan_id' => $id->plan_id, 'amount' => $exam_price->neco, 'id' => $id->id];
                        }
                    }

                    if ($id->exam_name == 'NABTEB') {
                        if ($exam_lock->nabteb == 1) {
                            $exam_plan[] = ['network' => $id->exam_name, 'plan_id' => $id->plan_id, 'amount' => $exam_price->nabteb, 'id' => $id->id];
                        }
                    }
                }
                return response()->json([
                    'status' => 'success',
                    'data' => $exam_plan
                ]);
            } else {
                return response()->json([
                    'message' => 'Not Avialable'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }
    }

    public function CablePlan(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
            if (DB::table('cable_id')->where('plan_id', $request->cable)->count() == 1) {
                $cable_plan = [];
                $cable_id = DB::table('cable_id')->where('plan_id', $request->cable)->first();
                $cable_get = DB::table('cable_plan')->where(['plan_status' => 1, 'cable_name' => $cable_id->cable_name])->get();
                foreach ($cable_get as $plan) {
                    $cable_plan[] = ['id' => $plan->id, 'name' => $plan->plan_name . ' ' . '₦' . number_format($plan->plan_price, 2), 'amount' => $plan->plan_price, 'plan_id' => $plan->plan_id];
                }
                return response()->json(
                    [
                        'data' => $cable_plan
                    ]
                );
            } else {
                return response()->json([
                    'message' => 'Cable Required'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }
    }

    public function PriceList(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
            $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifyapptoken($request->user_id)]);
            if ($check_user->count() == 1) {
                $adex = $check_user->first();
                // validate user type
                if ($adex->type == 'SMART') {
                    $user_type = 'smart';
                    $mtn_vtu = 'mtn_vtu_smart';
                    $mobile_vtu = 'mobile_vtu_smart';
                    $airtel_vtu = 'airtel_vtu_smart';
                    $glo_vtu = 'glo_vtu_smart';

                    $mtn_share = 'mtn_share_smart';
                    $mobile_share = 'mobile_share_smart';
                    $airtel_share = 'airtel_share_smart';
                    $glo_share = 'glo_share_smart';
                } else if ($adex->type == 'AGENT') {
                    $user_type = 'agent';


                    $mtn_vtu = 'mtn_vtu_agent';
                    $mobile_vtu = 'mobile_vtu_agent';
                    $airtel_vtu = 'airtel_vtu_agent';
                    $glo_vtu = 'glo_vtu_agent';

                    $mtn_share = 'mtn_share_agent';
                    $mobile_share = 'mobile_share_agent';
                    $airtel_share = 'airtel_share_agent';
                    $glo_share = 'glo_share_agent';
                } else if ($adex->type == 'AWUF') {
                    $user_type = 'awuf';

                    $mtn_vtu = 'mtn_vtu_awuf';
                    $mobile_vtu = 'mobile_vtu_awuf';
                    $airtel_vtu = 'airtel_vtu_awuf';
                    $glo_vtu = 'glo_vtu_awuf';

                    $mtn_share = 'mtn_share_awuf';
                    $mobile_share = 'mobile_share_awuf';
                    $airtel_share = 'airtel_share_awuf';
                    $glo_share = 'glo_share_awuf';
                } else if ($adex->type == 'API') {
                    $user_type = 'api';

                    $mtn_vtu = 'mtn_vtu_api';
                    $mobile_vtu = 'mobile_vtu_api';
                    $airtel_vtu = 'airtel_vtu_api';
                    $glo_vtu = 'glo_vtu_api';

                    $mtn_share = 'mtn_share_api';
                    $mobile_share = 'mobile_share_api';
                    $airtel_share = 'airtel_share_api';
                    $glo_share = 'glo_share_api';
                } else {
                    $user_type = 'special';

                    $mtn_vtu = 'mtn_vtu_special';
                    $mobile_vtu = 'mobile_vtu_special';
                    $airtel_vtu = 'airtel_vtu_special';
                    $glo_vtu = 'glo_vtu_special';

                    $mtn_share = 'mtn_share_special';
                    $mobile_share = 'mobile_share_special';
                    $airtel_share = 'airtel_share_special';
                    $glo_share = 'glo_share_special';
                }
                $all_plan = DB::table('data_plan')->where(['plan_status' => 1]);
                if ($all_plan->count() > 0) {
                    foreach ($all_plan->get() as  $plan) {
                        $data_plan[] =  ['plan' => $plan->plan_name . $plan->plan_size . ' ' . $plan->plan_type, 'network' => $plan->network,  'price' => '₦' . number_format($plan->$user_type, 2), 'id' => $plan->id];;
                    }
                } else {
                    $data_plan = [];
                }
                $airtime = DB::table('airtime_discount')->first();
                $airtime_plan = [];
                $airtime_plan[] = ['network' => 'MTN VTU', 'percentage' => $airtime->$mtn_vtu];
                $airtime_plan[] = ['network' => 'AIRTEL VTU', 'percentage' => $airtime->$airtel_vtu];
                $airtime_plan[] = ['network' => 'GLO VTU', 'percentage' => $airtime->$glo_vtu];
                $airtime_plan[] = ['network' => '9MOBILE VTU', 'percentage' => $airtime->$mobile_vtu];


                $airtime_plan[] = ['network' => 'MTN SNS', 'percentage' => $airtime->$mtn_share];
                $airtime_plan[] = ['network' => 'AIRTEL SNS', 'percentage' => $airtime->$airtel_share];
                $airtime_plan[] = ['network' => 'GLO SNS', 'percentage' => $airtime->$glo_share];
                $airtime_plan[] = ['network' => '9MOBILE SNS', 'percentage' => $airtime->$mobile_share];
                $cable_plan = [];
                foreach (DB::table('cable_plan')->where(['plan_status' => 1])->get() as $plan) {
                    $cable_plan[] = ['cable_name' => $plan->cable_name, 'plan_name' => $plan->plan_name, 'plan_price' => '₦' . number_format($plan->plan_price, 2)];
                }

                $exam_list = [];
                $exam_id = DB::table('exam_id')->get();
                $exam_price = DB::table('result_charge')->first();
                foreach ($exam_id as $exam) {
                    if ($exam->exam_name == 'WAEC') {
                        $exam_list[] = ['exam_name' => $exam->exam_name,  'amount' => '₦' . number_format($exam_price->waec, 2)];
                    }
                    if ($exam->exam_name == 'NECO') {
                        $exam_list[] = ['exam_name' => $exam->exam_name,  'amount' => '₦' . number_format($exam_price->neco, 2)];
                    }

                    if ($exam->exam_name == 'NABTEB') {
                        $exam_list[] = ['exam_name' => $exam->exam_name,  'amount' => '₦' . number_format($exam_price->nabteb, 2)];
                    }
                }
                return response()->json([
                    'status' => 'success',
                    'data' => $data_plan,
                    'airtime' => $airtime_plan,
                    'cable' => $cable_plan,
                    'exam' => $exam_list
                ]);
            } else {
                return redirect(env('ERROR_500'));
                return response()->json([
                    'status' => 403,
                    'message' => 'Unable to Authenticate System'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }
    }
    public function Transaction(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
            $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifyapptoken($request->user_id)]);
            if ($check_user->count() == 1) {
                $user = $check_user->first();
                $trans_history = [];
                $data_trans = [];
                $airtime_trans = [];
                $cable_trans = [];
                $bill_trans = [];
                $exam_trans = [];
                $deposit_trans = [];
                foreach (DB::table('message')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate(200) as $plan) {
                    if ($plan->plan_status == 1) {
                        $status = 'success';
                    } else if ($plan->plan_status == 2) {
                        $status = 'fail';
                    } else if ($plan->plan_status == 0) {
                        $status = 'processing';
                    } else {
                        $status = 'undefined';
                    }

                    $trans_history[] = ['transid' => $plan->transid, 'amount' => '₦' . number_format($plan->amount, 2), 'status' => $status, 'oldbal' => '₦' . number_format($plan->oldbal, 2), 'newbal' => '₦' . number_format($plan->newbal, 2), 'date' => $plan->adex_date, 'message' => $plan->message];
                }

                foreach (DB::table('data')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate(200) as $plan) {
                    if ($plan->plan_status == 1) {
                        $status = 'success';
                    } else if ($plan->plan_status == 2) {
                        $status = 'fail';
                    } else if ($plan->plan_status == 0) {
                        $status = 'processing';
                    } else {
                        $status = 'undefined';
                    }

                    $data_trans[] = ['transid' => $plan->transid, 'network' => $plan->network, 'plan_name' => $plan->plan_name, 'plan_type' => $plan->network_type, 'phone' => $plan->plan_phone, 'amount' => '₦' . number_format($plan->amount, 2), 'status' => $status, 'oldbal' => '₦' . number_format($plan->oldbal, 2), 'newbal' => '₦' . number_format($plan->newbal, 2), 'date' => $plan->plan_date, 'api_response' => $plan->api_response];
                }
                foreach (DB::table('airtime')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate(200) as $plan) {
                    if ($plan->plan_status == 1) {
                        $status = 'success';
                    } else if ($plan->plan_status == 2) {
                        $status = 'fail';
                    } else if ($plan->plan_status == 0) {
                        $status = 'processing';
                    } else {
                        $status = 'undefined';
                    }

                    $airtime_trans[] = ['transid' => $plan->transid, 'network' => $plan->network, 'network_type' => $plan->network_type, 'phone' => $plan->plan_phone, 'amount' => '₦' . number_format($plan->amount, 2), 'status' => $status, 'oldbal' => '₦' . number_format($plan->oldbal, 2), 'newbal' => '₦' . number_format($plan->newbal, 2), 'date' => $plan->plan_date, 'discount' => '₦' . number_format($plan->discount, 2),];
                }
                foreach (DB::table('cable')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate(200) as $plan) {
                    if ($plan->plan_status == 1) {
                        $status = 'success';
                    } else if ($plan->plan_status == 2) {
                        $status = 'fail';
                    } else if ($plan->plan_status == 0) {
                        $status = 'processing';
                    } else {
                        $status = 'undefined';
                    }

                    $cable_trans[] = ['transid' => $plan->transid, 'cable_name' => $plan->cable_name, 'plan_name' => $plan->cable_plan, 'iuc' => $plan->iuc, 'amount' => '₦' . number_format($plan->amount, 2), 'status' => $status, 'oldbal' => '₦' . number_format($plan->oldbal, 2), 'newbal' => '₦' . number_format($plan->newbal, 2), 'date' => $plan->plan_date, 'charges' => '₦' . number_format($plan->charges, 2), 'customer_name' => $plan->customer_name];
                }
                foreach (DB::table('bill')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate(200) as $plan) {
                    if ($plan->plan_status == 1) {
                        $status = 'success';
                    } else if ($plan->plan_status == 2) {
                        $status = 'fail';
                    } else if ($plan->plan_status == 0) {
                        $status = 'processing';
                    } else {
                        $status = 'undefined';
                    }

                    $bill_trans[] = ['transid' => $plan->transid, 'disco' => $plan->disco_name, 'meter_type' => $plan->meter_type, 'meter_number' => $plan->meter_number, 'amount' => '₦' . number_format($plan->amount, 2), 'status' => $status, 'oldbal' => '₦' . number_format($plan->oldbal, 2), 'newbal' => '₦' . number_format($plan->newbal, 2), 'date' => $plan->plan_date, 'charges' => '₦' . number_format($plan->charges, 2), 'customer_name' => $plan->customer_name, 'purchase_code' => $plan->token];
                }
                foreach (DB::table('exam')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate(200) as $plan) {
                    if ($plan->plan_status == 1) {
                        $status = 'success';
                    } else if ($plan->plan_status == 2) {
                        $status = 'fail';
                    } else if ($plan->plan_status == 0) {
                        $status = 'processing';
                    } else {
                        $status = 'undefined';
                    }

                    $exam_trans[] = ['transid' => $plan->transid, 'exam_name' => $plan->exam_name, 'quantity' => $plan->quantity, 'amount' => '₦' . number_format($plan->amount, 2), 'status' => $status, 'oldbal' => '₦' . number_format($plan->oldbal, 2), 'newbal' => '₦' . number_format($plan->newbal, 2), 'date' => $plan->plan_date,  'purchase_code' => $plan->purchase_code];
                }
                foreach (DB::table('deposit')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate(200) as $plan) {
                    if ($plan->status == 1) {
                        $status = 'success';
                    } else if ($plan->status == 2) {
                        $status = 'fail';
                    } else if ($plan->status == 0) {
                        $status = 'processing';
                    } else {
                        $status = 'undefined';
                    }

                    $deposit_trans[] = ['transid' => $plan->transid, 'type' => $plan->type, 'wallet_type' => $plan->wallet_type, 'amount' => '₦' . number_format($plan->amount, 2), 'status' => $status, 'oldbal' => '₦' . number_format($plan->oldbal, 2), 'newbal' => '₦' . number_format($plan->newbal, 2), 'date' => $plan->date,  'charges' => '₦' . number_format($plan->charges, 2)];
                }
                return response()->json([
                    'status' => 'success',
                    'trans_history' => $trans_history,
                    'data' => $data_trans,
                    'airtime' => $airtime_trans,
                    'cable' => $cable_trans,
                    'bill' => $bill_trans,
                    'exam' => $exam_trans,
                    'deposit' => $deposit_trans
                ]);
            } else {
                return redirect(env('ERROR_500'));
                return response()->json([
                    'status' => 403,
                    'message' => 'Unable to Authenticate System'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }
    }

    public function ProfileImage(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
            $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifyapptoken($request->user_id)]);
            if ($check_user->count() == 1) {
                $user = $check_user->first();
                if ($request->has('image')) {
                    $image = $request->file('image');
                    $save_here = 'profile_image';
                    $profile_image_name = $user->username . '_' . $image->getClientOriginalName();

                    $path = $request->file('image')->storeAs($save_here, $profile_image_name);
                    DB::table('user')->where(['id' => $user->id])->update(['profile_image' => asset('storage/' . $path)]);
                    
                    $user = DB::table('user')->where(['status' => 1, 'id' => $user->id])->first();
                      $user_details = [
                        'username' => $user->username,
                        'name'  => $user->name,
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'bal' => number_format($user->bal, 2),
                        'refbal' => number_format($user->refbal, 2),
                        'kyc' => $user->kyc,
                        'type' => $user->type,
                        'pin' => $user->pin,
                        'profile_image' => $user->profile_image,
                        'sterlen' => $user->sterlen,
                        'fed' => $user->fed,
                        'wema' => $user->wema,
                        'rolex' => $user->rolex,
                        'address' => $user->address,
                        'webhook' => $user->webhook,
                        'about' => $user->about,
                        'apikey' => $user->apikey,
                        
                        'notif' => DB::table('notif')->where(['username' => $user->username, 'adex' => 0])->count(),
                       
                         
                    ];
                    return response()->json([
                        'status' =>  'success',
                        'user' => $user_details
                    ]);
                } else {
                    return response()->json([
                        'status' => 'fail',
                        'message' => 'Image File Empty'
                    ])->setStatusCode(403);
                }
            } else {
                return redirect(env('ERROR_500'));
                return response()->json([
                    'status' => 403,
                    'message' => 'Unable to Authenticate System'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }
    }

    public function Notification(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
            $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifyapptoken($request->user_id)]);
            if ($check_user->count() == 1) {
                $user = $check_user->first();
                DB::table('notif')->where(['username' => $user->username])->update(['adex' => 1]);
                return response()->json([
                    'data' => DB::table('notif')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate(100)
                ]);
            } else {
                return redirect(env('ERROR_500'));
                return response()->json([
                    'status' => 403,
                    'message' => 'Unable to Authenticate System'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }
    }
    public function NewPin(Request $request){
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
        $validator = Validator::make($request->all(), [
                'app_key' => 'required|string',
                'transaction_pin' => 'required|digits:4',
            ], [
                'transaction_pin.required' => 'Transaction Pin Required',
                'transaction_pin.digits' => 'Transaction Pin Digit Must Be 4 Digits'
            ]);
              if ($validator->fails()) {
                return response()->json([
                    'status' => 403,
                    'message' => $validator->errors()->first()
                ])->setStatusCode(403);
            } else {
                if(DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 1])->count() == 1){
                    $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 1])->first();
                    
                    DB::table('user')->where(['id' => $user->id])->update(['pin' => $request->transaction_pin, 'otp' => null]);
                         $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 1])->first();
                   
                    return response()->json([
                        'status' =>  'success',
                       
                    ]);
            }else{
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Invalid User'
                    ])->setStatusCode(403);
            }
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }
    }
    public function CompleteProfile(Request $request){
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
        $validator = Validator::make($request->all(), [
                'app_key' => 'required|string',
                'transaction_pin' => 'required|digits:4',
              
            ], [
                'transaction_pin.required' => 'Transaction Pin Required',
                'transaction_pin.digits' => 'Transaction Pin Digit Must Be 4 Digits'
            ]);
              if ($validator->fails()) {
                return response()->json([
                    'status' => 403,
                    'message' => $validator->errors()->first()
                ])->setStatusCode(403);
            } else {
                if(DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 1])->count() == 1){
                    $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 1])->first();
                    DB::table('user')->where(['id' => $user->id])->update(['pin' => $request->transaction_pin]);
                         $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 1])->first();
                         $email_data = [
                            'name' => $user->name,
                            'email' => $user->email,
                            'username' => $user->username,
                            'title' => 'WELCOME EMAIL',
                            'sender_mail' => $this->general()->app_email,
                            'system_email' => $this->general()->app_email,
                            'app_name' => $this->general()->app_name,
                            'pin' => $user->pin,
                        ];
                        MailController::send_mail($email_data, 'email.welcome');
                             $user_details = [
                        'username' => $user->username,
                        'name'  => $user->name,
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'bal' => number_format($user->bal, 2),
                        'refbal' => number_format($user->refbal, 2),
                        'kyc' => $user->kyc,
                        'type' => $user->type,
                        'pin' => $user->pin,
                        'profile_image' => $user->profile_image,
                        'sterlen' => $user->sterlen,
                        'fed' => $user->fed,
                        'wema' => $user->wema,
                        'rolex' => $user->rolex,
                        'address' => $user->address,
                        'webhook' => $user->webhook,
                        'about' => $user->about,
                        'apikey' => $user->apikey,
                        
                        'notif' => DB::table('notif')->where(['username' => $user->username, 'adex' => 0])->count(),
                       
                         
                    ];
                    return response()->json([
                        'status' =>  'success',
                        'user' => $user_details
                    ]);
           
            }else{
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Invalid User'
                    ])->setStatusCode(403);
            }
            }
        } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }
        
    }
    
    public function DepositTransaction(Request $request){
         $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
             if(DB::table('user')->where(['id' => $this->verifyapptoken($request->user_id), 'status' => 1])->count() == 1){
                 $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->user_id), 'status' => 1])->first();
                 $trans = [];
                 
                 if($request->post_number == '10'){
                $trans = DB::table('message')->where(function($function){
                      $function->orWhere('role', 'debit')->orWhere('role', 'credit');
                 })->where('username', $user->username)->orderBy('id', 'desc')->get()->take(10);
                 }else{
                         $trans = DB::table('message')->where(function($function){
                      $function->orWhere('role', 'debit')->orWhere('role', 'credit');
                 })->where('username', $user->username)->orderBy('id', 'desc')->get()->take(20);
                 }
                 return response()->json([
                     'status' => 'success',
                     'trans' => $trans
                     ]);
             }else{
                 return response()->json([
                     'status' => 'fail',
                     'message' => 'Invalid User'
                     ])->setStatusCode(403);
             }
         } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }   
    }
    public function TransactionInvoice(Request $request){
         $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
             if(DB::table('user')->where(['id' => $this->verifyapptoken($request->user_id), 'status' => 1])->count() == 1){
                 $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->user_id), 'status' => 1])->first();
               if(DB::table('message')->where(['transid' => $request->transid, 'username' => $user->username])->count() == 1){
                 $main_trans = DB::table('message')->where(['transid' => $request->transid, 'username' => $user->username])->first();
                 if($main_trans->role == 'data'){
                    $return_trans = [
                        'main_trans' => $main_trans,
                        'data' => DB::table('data')->where(['username' => $user->username, 'transid' => $main_trans->transid])->first()
                        ];
                 }else if($main_trans->role == 'airtime'){
                    $return_trans = [
                        'main_trans' => $main_trans,
                        'data' => DB::table('airtime')->where(['username' => $user->username, 'transid' => $main_trans->transid])->first()
                        ]; 
                 } else if($main_trans->role == 'credit'){
                     $return_trans = [
                        'main_trans' => $main_trans,
                        'data' => DB::table('deposit')->where(['username' => $user->username, 'transid' => $main_trans->transid])->first()
                        ];
                 }else if($main_trans->role == 'cash'){
                   $return_trans = [
                        'main_trans' => $main_trans,
                        'data' => DB::table('cash')->where(['username' => $user->username, 'transid' => $main_trans->transid])->first()
                        ];
                 }else if($main_trans->role == 'bill'){
                     
                 $return_trans = [
                        'main_trans' => $main_trans,
                        'data' => DB::table('bill')->where(['username' => $user->username, 'transid' => $main_trans->transid])->first()
                        ];
                        
                        }else if($main_trans->role == 'cable'){
                         $return_trans = [
                        'main_trans' => $main_trans,
                        'data' => DB::table('cable')->where(['username' => $user->username, 'transid' => $main_trans->transid])->first()
                        ]; 
                        }else if($main_trans->role == 'exam'){
                          $return_trans = [
                        'main_trans' => $main_trans,
                        'data' => DB::table('exam')->where(['username' => $user->username, 'transid' => $main_trans->transid])->first()
                        ];  
                        
                        }else if($main_trans->role == 'data_card'){
                          $return_trans = [
                        'main_trans' => $main_trans,
                        'data' => DB::table('data_card')->where(['username' => $user->username, 'transid' => $main_trans->transid])->first()
                        ];  
                        }else if($main_trans->role == 'recharge_card'){
                            
                          $return_trans = [
                        'main_trans' => $main_trans,
                        'data' => DB::table('recharge_card')->where(['username' => $user->username, 'transid' => $main_trans->transid])->first()
                        ];  
                 }else {
                     $return_trans = [
                        'main_trans' => $main_trans
                        ];
                 }
                 
                 return response()->json([
                     'status' => 'success',
                     'trans' => $return_trans
                     ]);
               }else{
                   return response()->json([
                       'message' => 'Transaction ID Not Found'
                       ])->setStatusCode(403);
               }  
             }else{
                 return response()->json([
                     'message' => 'User Not Authorized'
                     ])->setStatusCode(403);
             }
         } else {
            return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
        }    
    }
    public function TransactionHistoryAdex(Request $request){
           $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
        $validator = Validator::make($request->all(), [
        'app_key' => 'required|string',
            'type' => 'required|string',
            ]);
            
             if(DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 1])->count() == 1){
                 $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 1])->first();
             $search = strtolower($request->search);
            // the transaction type  (data as output)
            if($request->type == 'data'){
            if(empty($search)){
                return response()->json([
                      'data' => DB::table('data')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate(25)
                                ]);
            }else{
              return response()->json([
                      'data' => DB::table('data')->where(['username' => $user->username])->where(function ($query) use ($search) {
                                    $query->orWhere('network', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('api_response', 'LIKE', "%$search%")->orWhere('plan_phone', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('network_type', 'LIKE', "%$search%")->orWhere('wallet', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate(25)
                                ]);  
            }
            
            // the transaction type (airtime output)
            }else if($request->type == 'airtime'){
                
            if(empty($search)){
                return response()->json([
                      'data' => DB::table('airtime')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate(25)
                                ]);
            }else{
              return response()->json([
                      'data' => DB::table('airtime')->where(['username' => $user->username])->where(function ($query) use ($search) {
                                    $query->orWhere('network', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('plan_phone', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('network_type', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate(25)
                                ]);  
            }
            
            }else if($request->type == 'deposit'){
                
             if(empty($search)){
                return response()->json([
                      'data' => DB::table('deposit')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate(25)
                                ]);
            }else{
              return response()->json([
                      'data' => DB::table('deposit')->where(['username' => $user->username])->Where(function ($query) use ($search) {
                                    $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('wallet_type', 'LIKE', "%$search%")->orWhere('type', 'LIKE', "%$search%")->orWhere('credit_by', 'LIKE', "%$search%")->orWhere('charges', 'LIKE', "%$search%")->orWhere('monify_ref', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate(25)
                        ]);
            }
            
            }else if($request->type == 'cash'){
                if(empty($search)){
                    
                    return response()->json([
                      'data' => DB::table('cash')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate(25)
                                ]);
                }else{
                        return response()->json([
                      'data' => DB::table('cash')->where(['username' => $user->username])->Where(function ($query) use ($search) {
                                        $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('amount_credit', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('payment_type', 'LIKE', "%$search%")->orWhere('network', 'LIKE', "%$search%")->orWhere('sender_number', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate(25)
                                ]);
                }
                
            }elseif($request->type == 'bill'){
                
             if(empty($search)){
                 return response()->json([
                      'data' => DB::table('bill')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate(25)
                             ]);
             }else{
                  return response()->json([
                      'data' => DB::table('bill')->where(['username' => $user->username])->Where(function ($query) use ($search) {
                                        $query->orWhere('disco_name', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('meter_number', 'LIKE', "%$search%")->orWhere('meter_type', 'LIKE', "%$search%")->orWhere('customer_name', 'LIKE', "%$search%")->orWhere('token', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate(25)
                                ]);
                 
             }
             
            }elseif($request->type == 'earning'){
            if(empty($search)){
                  return response()->json([
                      'data' => DB::table('message')->where(['username' => $user->username, 'role' => 'earning'])->orderBy('id', 'desc')->paginate(25)
                             ]);
            }else{
                  return response()->json([
                      'data' => DB::table('message')->where(['username' => $user->username, 'role' => 'earning'])->Where(function ($query) use ($search) {
                                        $query->orWhere('adex_date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate(25)
                             ]);
            }
            
            }else if($request->type == 'cable'){
                
            if(empty($search)){
                 return response()->json([
                      'data' => DB::table('cable')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate(25)
                             ]);
            }else{
               return response()->json([
                      'data' => DB::table('cable')->where(['username' => $user->username])->Where(function ($query) use ($search) {
                                        $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('charges', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('cable_plan', 'LIKE', "%$search%")->orWhere('cable_name', 'LIKE', "%$search%")->orWhere('iuc', 'LIKE', "%$search%")->orWhere('customer_name', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate(25)
                             ]);  
            }
            
            }else if($request->type == 'exam'){
                
             if(empty($search)){
                 return response()->json([
                      'data' => DB::table('exam')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate(25)
                             ]);
            }else{
               return response()->json([
                      'data' => DB::table('exam')->where(['username' => $user->username])->Where(function ($query) use ($search) {
                                        $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('purchase_code', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('exam_name', 'LIKE', "%$search%")->orWhere('quantity', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate(25)
                             ]);  
            }
            
            }else if($request->type == 'data_card'){
            if(empty($search)){
                
                return response()->json([
                      'data' => DB::table('data_card')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate(25)
                             ]);
            }else{
                return response()->json([
                      'data' => DB::table('data_card')->Where(function ($query) use ($search) {
                                        $query->orWhere('username', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('load_pin', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('plan_type', 'LIKE', "%$search%")->orWhere('card_name', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%");
                                    })->where(['username' => $user->username])->orderBy('id', 'desc')->paginate(25)
                             ]);
                
            }
            
            }else if($request->type == 'recharge_card'){
            if(empty($search)){
                
                return response()->json([
                      'data' => DB::table('recharge_card')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate(25)
                             ]);
            }else{
                return response()->json([
                      'data' => DB::table('recharge_card')->where(['username' => $user->username])->Where(function ($query) use ($search) {
                                        $query->orWhere('username', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('load_pin', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('card_name', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate(25)
                             ]);
                
            }
             
            }else{
                
                 if(empty($search)){
                  return response()->json([
                      'data' => DB::table('message')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate(25)
                             ]);
            }else{
                  return response()->json([
                      'data' => DB::table('message')->where(['username' => $user->username])->Where(function ($query) use ($search) {
                                        $query->orWhere('adex_date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('message', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate(25)
                             ]);
            }
            }
            
            
              if ($validator->fails()) {
                return response()->json([
                    'status' => 403,
                    'message' => $validator->errors()->first()
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'message' => 'User Not Found',
            ])->setStatusCode(502);  
        }
    } else {
        return response()->json([
            'message' => 'APP Server Down',
        ])->setStatusCode(505);
    }
    }
    
    public function SendOtp(Request $request){
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
             if(DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 1])->count() == 1){
                 $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->app_key), 'status' => 1])->first();
                    $otp = random_int(1000, 9999);
                            $data = [
                                'otp' => $otp
                            ];
                            $tableid = [
                                'id' => $user->id
                            ];
                            $this->updateData($data, 'user', $tableid);
                            $email_data = [
                                'name' => $user->name,
                                'email' => $user->email,
                                'username' => $user->username,
                                'title' => 'Account Verification',
                                'pin' => $user->pin,
                                'sender_mail' => env('MAIL_FROM_ADDRESS'),
                                'app_name' => env('APP_NAME'),
                                'otp' => $otp
                            ];
                            MailController::send_mail($email_data, 'email.reset_pin');
                            return response()->json([
                                 'status' => 'success',
                                 'otp' => $otp
                                ]);
                            
             }else{
                 return response()->json([
                'message' => 'User Not Found',
            ])->setStatusCode(502);  
             }
            
           }else{
                return response()->json([
                'message' => 'APP Server Down',
            ])->setStatusCode(505);
           } 
        
    }
    
   public function AppSystemNotification(Request $request){
    $authHeader = $request->header('Authorization');
    if (strpos($authHeader, 'Token ') === 0) {
        $authHeader = substr($authHeader, 6);
    }
    $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
    if ($user) {
      $request->validate([
    'app_key' => 'required|string',
]);

    $paginationLimit = 25;
    $records = DB::table('notif')
        ->where(['username' => $user->username])
        ->orderBy('id', 'desc')
        ->paginate($paginationLimit);

    // Update all retrieved records
    foreach ($records as $record) {
        DB::table('notif')
            ->where('id', $record->id)
            ->update(['adex' => 1]);
    }

    // Return the updated records
    return response()->json(['data' => $records], 200);
    } else {
        return response()->json([
            'message' => 'User Not Found',
        ])->setStatusCode(502);    
    }
}
    
    public function ClearNotification(Request $request) {
         $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
             $request->validate([
    'user_id' => 'required|string',
]);
if (env('ADEX_DEVICE_KEY') == $request->header('Authorization')) {
     $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->user_id), 'status' => 1])->first();
      if($user){
         DB::table('notif')->where(['username' => $user->username])->delete(); 
         
         return response()->json([
             'status' => 'success'
             ], 200);
      }
     return response()->json([
                'message' => 'User Not Found',
            ])->setStatusCode(502);   
}
  return response()->json([
        'message' => 'APP Server Down',
    ])->setStatusCode(505);
    }
    }

    public function recentTransaction(Request $request){
        $authHeader = $request->header('Authorization');
        if (strpos($authHeader, 'Token ') === 0) {
            $authHeader = substr($authHeader, 6);
        }
        $user = DB::table('user')->where('apikey', $authHeader)->orWhere('app_key', $authHeader)->first();
        if ($user) {
        $request->validate([
    'user_id' => 'required|string',
]); 
if (env('ADEX_DEVICE_KEY') == $request->header('Authorization')) {
     $user = DB::table('user')->where(['id' => $this->verifyapptoken($request->user_id), 'status' => 1])->first();
      if($user){
          return response()->json([
             'status' => 'success',
             'data' => DB::table('message')
    ->where('username', $user->username)
    ->orderBy('id', 'desc')
    ->limit(5)
    ->select('role', 'amount', 'transid', 'adex_date', 'plan_status')
    ->get()
             ], 200);
      }
      return response()->json([
                'message' => 'User Not Found',
            ])->setStatusCode(502);   
}
return response()->json([
        'message' => 'APP Server Down',
    ])->setStatusCode(505);
    }
}
}
