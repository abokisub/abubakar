<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MailController;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class  AdminController extends Controller
{

    public function userRequest(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {

                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    // user request
                    $user_request = DB::table('request')->select('username', 'message', 'date', 'transid', 'status', 'title', 'transid', 'id');
                    if ($user_request->count() > 0) {
                        foreach ($user_request->orderBy('id', 'desc')->get() as $adex) {
                            $select_user = DB::table('user')->where('username', $adex->username);
                            if ($select_user->count() > 0) {
                                $users = $select_user->first();
                                if ($users->profile_image !== null) {
                                    $profile_image[] = ['username' => $adex->username, 'transid' => $adex->transid, 'title' => $adex->title, 'id' => $adex->id, 'message' => $adex->message, 'date' => $adex->date, 'profile_image' => $users->profile_image, 'status' => $adex->status];
                                } else {
                                    $profile_image[] = ['username' => $adex->username, 'transid' => $adex->transid, 'title' => $adex->title, 'id' => $adex->id, 'message' => $adex->message, 'date' => $adex->date, 'profile_image' => $users->username, 'status' => $adex->status];
                                }
                            } else {
                                $profile_image[] = ['username' => $adex->username, 'transid' => $adex->transid, 'title' => $adex->title, 'id' => $adex->id, 'message' => $adex->message, 'date' => $adex->date, 'profile_image' => $adex->username, 'status' => $adex->status];
                            }
                        }
                        return response()->json([
                            'status' => 'success',
                            'notif' => $profile_image
                        ]);
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
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function ClearRequest(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    DB::table('request')->delete();

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Done'
                    ]);
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function UserSystem(Request $request)
    {
        $allowedOrigins = array_filter(array_map('trim', explode(',', config('adex.app_key', ''))));
        $origin = $request->headers->get('origin');
        $originNormalized = rtrim($origin ?: '', '/');
        $referer = $request->headers->get('referer');
        $host = $request->getHost();
        $scheme = $request->getScheme();
        $fullUrl = $scheme . '://' . $host;
        
        // Check if request is from same origin (no Origin header for same-origin requests)
        $isSameOrigin = empty($origin) && $referer && strpos($referer, $fullUrl) === 0;
        
        // Also check if the referer matches any allowed origin
        $refererMatches = false;
        if ($referer) {
            $refererUrl = parse_url($referer, PHP_URL_SCHEME) . '://' . parse_url($referer, PHP_URL_HOST);
            $refererNormalized = rtrim($refererUrl ?: '', '/');
            $refererMatches = in_array($refererNormalized, $allowedOrigins);
        }
        
        // Allow if: origin matches, referer matches, same-origin request, or device key matches
        if (in_array($originNormalized, $allowedOrigins) 
            || $refererMatches 
            || $isSameOrigin 
            || config('adex.device_key') === $request->header('Authorization')
            || in_array($fullUrl, $allowedOrigins)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {

                    $users_info = [
                        'wallet_balance' => DB::table('user')->sum('bal') ?: 0,
                        'ref_balance' => DB::table('user')->sum('refbal') ?: 0,
                        'all_user' => DB::table('user')->count(),
                        'smart_total' => DB::table('user')->where('type', 'SMART')->count(),
                        'awuf_total' => DB::table('user')->where('type', 'AWUF')->count(),
                        'special_total' => DB::table('user')->where('type', 'SPECIAL')->count(),
                        'api_total' => DB::table('user')->where('type', 'API')->count(),
                        'agent_total' => DB::table('user')->where('type', 'AGENT')->count(),
                        'customer_total' => DB::table('user')->where('type', 'CUSTOMER')->count(),
                        'admin_total' => DB::table('user')->where('type', 'ADMIN')->count(),
                        'active_user' => DB::table('user')->where('status', 1)->count(),
                        'deactivate_user' => DB::table('user')->where('status', 3)->count(),
                        'banned_user' => DB::table('user')->where('status', 2)->count(),
                        'unverified_user' => DB::table('user')->where('status', 0)->count(),
                        'mtn_cg_bal' => DB::table('wallet_funding')->sum('mtn_cg_bal') ?: 0,
                        'mtn_g_bal' => DB::table('wallet_funding')->sum('mtn_g_bal') ?: 0,
                        'mtn_sme_bal' => DB::table('wallet_funding')->sum('mtn_sme_bal') ?: 0,
                        'airtel_cg_bal' => DB::table('wallet_funding')->sum('airtel_cg_bal') ?: 0,
                        'airtel_g_bal' => DB::table('wallet_funding')->sum('airtel_g_bal') ?: 0,
                        'airtel_sme_bal' => DB::table('wallet_funding')->sum('airtel_sme_bal') ?: 0,
                        'glo_cg_bal' => DB::table('wallet_funding')->sum('glo_cg_bal') ?: 0,
                        'glo_g_bal' => DB::table('wallet_funding')->sum('glo_g_bal') ?: 0,
                        'glo_sme_bal' => DB::table('wallet_funding')->sum('glo_sme_bal') ?: 0,
                        'mobile_cg_bal' => DB::table('wallet_funding')->sum('mobile_cg_bal') ?: 0,
                        'mobile_g_bal' => DB::table('wallet_funding')->sum('mobile_g_bal') ?: 0,
                        'mobile_sme_bal' => DB::table('wallet_funding')->sum('mobile_sme_bal') ?: 0,
                        'total_process' => DB::table('message')->where(['plan_status' => 0])->count(),
                        'total_data_proccess' => DB::table('data')->where(['plan_status' => 0])->count()
                    ];

                    return response()->json([
                        'status' => 'success',
                        'user' => $users_info,
                        'payment' => DB::table('adex_key')->first(),
                    ]);
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 403,
                    'message' => 'User ID required'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function editUserDetails(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    if (!empty($request->username)) {
                        $verify_user = DB::table('user')->where('id', $request->username);
                        if ($verify_user->count() == 1) {
                            return response()->json([
                                'status' => 'success',
                                'user' => $verify_user->first()
                            ]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'User ID Not Found'
                            ])->setStatusCode(403);
                        }
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'User ID Required'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function CreateNewUser(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    $main_validator = validator::make($request->all(), [
                        'name' => 'required|max:199|min:8',
                        'email' => 'required|unique:user,email|max:255|email',
                        'phone' => 'required|numeric|unique:user,phone|digits:11',
                        'password' => 'required|min:8',
                        'username' => 'required|unique:user,username|max:12|string|alpha_num',
                        'status' => 'required',
                        'type' => 'required'
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
                        'status.required' => 'Account Status Required',
                        'type.required' => 'Account Role Required'
                    ]);
                    //declaring user status
                    if ($request->status == 'Active' || $request->status == 1) {
                        $status = 1;
                    } else if ($request->status == 'Deactivate' || $request->status == 3) {
                        $status = 3;
                    } else if ($request->status == 'Banned' || $request->status == 2) {
                        $status = 2;
                    } else if ($request->status == 'Unverified' || $request->status == 0) {
                        $status = 0;
                    } else {
                        $status = 0;
                    }

                    //system kyc
                    if ($request->kyc == 'true') {
                        $kyc = 1;
                    } else {
                        $kyc = 0;
                    }
                    //checking referral username
                    if ($request->ref != null) {
                        $check_ref = DB::table('user')
                            ->where('username', '=', $request->ref)
                            ->count();
                    }
                    //profile_image
                    if ($request->hasFile('profile_image')) {
                        $validator = validator::make($request->all(), [
                            'profile_image' => 'required|image|max:2047|mimes:jpg,png,jpeg',
                        ]);
                        if ($validator->fails()) {
                            $path = null;
                            return response()->json([
                                'message' => $validator->errors()->first(),
                                'status' => 403
                            ])->setStatusCode(403);
                        } else {
                            $profile_image = $request->file('profile_image');
                            $profile_image_name = $request->username . '_' . $profile_image->getClientOriginalName();
                            $save_here = 'profile_image';
                            $path = $request->file('profile_image')->storeAs($save_here, $profile_image_name);
                        }
                    } else {
                        $path = null;
                    }
                    if ($main_validator->fails()) {
                        return response()->json([
                            'message' => $main_validator->errors()->first(),
                            'status' => 403
                        ])->setStatusCode(403);
                    } else if (substr($request->phone, 0, 1) != '0') {
                        return response()->json([
                            'message' => 'Invalid Phone Number',
                            'status' => 403
                        ])->setStatusCode(403);
                    } else if ($request->ref != null && $check_ref == 0) {
                        return response()->json([
                            'message' => 'Invalid Referral Username You can Leave the referral Box Empty',
                            'status' => '403'
                        ])->setStatusCode(403);
                    } elseif ($request->pin != null && !is_numeric($request->pin)) {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Transaction Pin Must be Numeric'
                        ])->setStatusCode(403);
                    } else if ($request->pin != null && strlen($request->pin) != 4) {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Transaction Pin Must be 4 Digit'
                        ])->setStatusCode(403);
                    } else {
                        // checking
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
                        $user->type = $request->type;
                        $user->date = Carbon::now("Africa/Lagos")->toDateTimeString();;
                        $user->kyc = $kyc;
                        $user->status = $status;
                        $user->user_limit = $this->adex_key()->default_limit;
                        $user->pin = $request->pin;
                        $user->webhook = $request->webhook;
                        $user->about = $request->about;
                        $user->address = $request->address;
                        $user->profile_image = $path ? asset('storage/' . $path) : null;
                        $user->save();
                        if ($user != null) {
                            $general = $this->general();
                            if ($status == 0 && $request->isVerified == false) {
                                $otp = random_int(100000, 999999);
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
                                    'title' => 'Account Verification',
                                    'sender_mail' => $general->app_email,
                                    'app_name' => env('APP_NAME'),
                                    'otp' => $otp
                                ];
                                MailController::send_mail($email_data, 'email.verify');
                            } else {
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
                            }
                            return response()->json([
                                'status' => 'success',
                                'message' => 'Account Created'
                            ]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Unable to Register User'
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
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function ChangeApiKey(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    if (DB::table('user')->where('username', $request->username)->count() > 0) {
                        if ($this->updateData(['apikey' => bin2hex(openssl_random_pseudo_bytes(30))], 'user', ['username' => $request->username])) {
                            return response()->json([
                                'status' => 'success',
                                'message' => 'ApiKey Upgraded'
                            ]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'An Error Occured'
                            ])->setStatusCode(403);
                        }
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Invalid User ID'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function EditUser(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    //validate all here
                    if (DB::table('user')->where(['id' => $request->user_id])->count() == 1) {
                        $main_validator = validator::make($request->all(), [
                            'name' => 'required',
                            'email' => "required|unique:user,email,$request->user_id",
                            'phone' => "required|numeric|unique:user,phone,$request->user_id|digits:11",
                            'status' => 'required',
                            'type' => 'required',
                            'user_limit' => 'required|numeric|digits_between:2,6',
                        ], [
                            'name.required' => 'Full Name is Required',
                            'email.required' => 'E-mail is Required',
                            'phone.required' => 'Phone Number Required',
                            'username.required' => 'Username Required',
                            'username.unique' => 'Username already Taken',
                            'phone.unique' => 'Phone Number already Taken',
                            'username.max' => 'Username Maximum Length is 12 ' . $request->username,
                            'email.unique' => 'Email Alreay Taken',
                            'password.min' => 'Password Not Strong Enough',
                            'name.min' => 'Invalid Full Name',
                            'name.max' => 'Invalid Full Name',
                            'phone.numeric' => 'Phone Number Must be Numeric ' . $request->phone,
                            'status.required' => 'Account Status Required',
                            'type.required' => 'Account Role Required'
                        ]);
                        //declaring user status
                        if ($request->status == 'Active' || $request->status == 1) {
                            $status = 1;
                        } else if ($request->status == 'Deactivate' || $request->status == 3) {
                            $status = 3;
                        } else if ($request->status == 'Banned' || $request->status == 2) {
                            $status = 2;
                        } else if ($request->status == 'Unverified' || $request->status == 0) {
                            $status = 0;
                        } else {
                            $status = 0;
                        }

                        //system kyc
                        if ($request->kyc == 'true') {
                            $kyc = 1;
                        } else {
                            $kyc = 0;
                        }
                        //checking referral username
                        if ($request->ref != null) {
                            $check_ref = DB::table('user')
                                ->where('username', '=', $request->ref)
                                ->count();
                        }
                        //profile_image
                        if ($request->hasFile('profile_image')) {
                            $validator = validator::make($request->all(), [
                                'profile_image' => 'required|image|max:2047|mimes:jpg,png,jpeg',
                            ]);
                            if ($validator->fails()) {
                                $path = null;
                                return response()->json([
                                    'message' => $validator->errors()->first(),
                                    'status' => 403
                                ])->setStatusCode(403);
                            } else {
                                $profile_image = $request->file('profile_image');
                                $profile_image_name = $request->username . '_' . $profile_image->getClientOriginalName();
                                $save_here = 'profile_image';
                                $stored_path = $request->file('profile_image')->storeAs($save_here, $profile_image_name);
                                $path = asset('storage/' . $stored_path);
                            }
                        } else {
                            $path = $request->profile_image;
                        }
                        if ($main_validator->fails()) {
                            return response()->json([
                                'message' => $main_validator->errors()->first(),
                                'status' => 403
                            ])->setStatusCode(403);
                        } else if (substr($request->phone, 0, 1) != '0') {
                            return response()->json([
                                'message' => 'Invalid Phone Number',
                                'status' => 403
                            ])->setStatusCode(403);
                        } else if ($request->ref != null && $check_ref == 0) {
                            return response()->json([
                                'message' => 'Invalid Referral Username You can Leave the referral Box Empty',
                                'status' => '403'
                            ])->setStatusCode(403);
                        } elseif ($request->pin != null && !is_numeric($request->pin)) {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Transaction Pin Must be Numeric'
                            ])->setStatusCode(403);
                        } else if ($request->pin != null && strlen($request->pin) != 4) {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Transaction Pin Must be 4 Digit'
                            ])->setStatusCode(403);
                        } else {
                            // updateing
                            $user = User::find($request->user_id);
                            $user->name = $request->name;
                            $user->email = $request->email;
                            $user->phone = $request->phone;
                            $user->ref = $request->ref;
                            $user->type = $request->type;
                            $user->kyc = $kyc;
                            $user->status = $status;
                            $user->user_limit = $request->user_limit;
                            $user->reason = $request->reason;
                            $user->pin = $request->pin;
                            $user->webhook = $request->webhook;
                            $user->about = $request->about;
                            $user->address = $request->address;
                            $user->profile_image = $path;
                            $user->sterlen = $request->sterlen;
                            $user->wema = $request->wema;
                            $user->rolex = $request->rolex;
                            $user->fed = $request->fed;
                            $user->otp = $request->otp;
                            $user->Update();
                            if ($user != null) {
                                $general = $this->general();
                                if ($status == 0 && $request->isVerified == false) {
                                    $otp = random_int(100000, 999999);
                                    $data = [
                                        'otp' => $otp
                                    ];
                                    $tableid = [
                                        'username' => $request->username
                                    ];
                                    $this->updateData($data, 'user', $tableid);
                                    $email_data = [
                                        'name' => $request->name,
                                        'email' => $request->email,
                                        'username' => $request->username,
                                        'title' => 'Account Verification',
                                        'sender_mail' => $general->app_email,
                                        'app_name' => env('APP_NAME'),
                                        'otp' => $otp
                                    ];
                                    MailController::send_mail($email_data, 'email.verify');
                                }
                                return response()->json([
                                    'status' => 'success',
                                    'message' => 'Updated Success'
                                ]);
                            } else {
                                return response()->json([
                                    'status' => 403,
                                    'message' => 'Unable to Update User'
                                ])->setStatusCode(403);
                            }
                        }
                    } else {
                        return response()->json([
                            'staus' => 403,
                            'message' => 'An Error Occured'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function FilterUser(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                });
                if ($check_user->count() > 0) {
                    $users = DB::table('user')->where('username', 'LIKE', "%$request->username%")->orWhere('email', 'LIKE', "%$request->username%")->orWhere('phone', 'LIKE', "%$request->username%")->orWhere('name', 'LIKE', "%$request->username%")->limit(10)
                        ->get();

                    return response()->json([
                        'status' => 'success',
                        'user' => $users
                    ]);
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function CreditUserAdex(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                });
                $admin = $check_user->first();
                $general = $this->general();
                $all_admin = DB::table('user')->where(['status' => 1])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                })->get();
                if ($check_user->count() > 0) {
                    $validator =   validator::make($request->all(), [
                        'user_username' => 'required|string',
                        'wallet' => 'required|string',
                        'amount' => 'required|numeric|integer|not_in:0|gt:0',
                        'credit' => 'required|string',
                        'reason' => 'required|string'
                    ], [
                        'credit.required' => 'Credit/Debit Required',
                        'wallet.required' => 'User Wallet Required'
                    ]);
                    //get which user
                    $user = DB::table('user')->where('username', $request->user_username);
                    $user_details = $user->first();
                    // wallet statement
                    if ($request->wallet == 'wallet') {
                        $wallet = 'User Wallet';
                    } else if ($request->wallet == 'mtn_cg_bal') {
                        $wallet = 'MTN CG WALLET';
                    } else if ($request->wallet == 'mtn_g_bal') {
                        $wallet = 'MTN GIFTING WALLET';
                    } else if ($request->wallet == 'mtn_sme_bal') {
                        $wallet = 'MTN SME WALLET';
                    } else if ($request->wallet == 'airtel_cg_bal') {
                        $wallet = 'AIRTEL CG WALLET';
                    } else if ($request->wallet == 'airtel_g_bal') {
                        $wallet = 'AIRTEL GIFTING WALLET';
                    } else if ($request->wallet == 'airtel_sme_bal') {
                        $wallet = 'AIRTEL SME WALLET';
                    } else if ($request->wallet == 'glo_cg_bal') {
                        $wallet = 'GLO CG WALLET';
                    } else if ($request->wallet == 'glo_g_bal') {
                        $wallet = 'GLO GIFTING WALLET';
                    } else if ($request->wallet == 'glo_sme_bal') {
                        $wallet = 'GLO SME WALLET';
                    } else if ($request->wallet == 'mobile_cg_bal') {
                        $wallet = '9MOBILE CG WALLET';
                    } else if ($request->wallet == 'mobile_g_bal') {
                        $wallet = '9MOBILE GIFTING WALLET';
                    } else if ($request->wallet == 'mobile_sme_bal') {
                        $wallet = '9MOBILE SME WALLET';
                    }
                    if ($validator->fails()) {
                        return response()->json([
                            'message' => $validator->errors()->first(),
                            'status' => 403
                        ])->setStatusCode(403);
                    } else if ($user->count() != 1) {
                        return response()->json([
                            'message' => 'Unable to Get the Correspond User Username',
                            'status' => 403
                        ])->setStatusCode(403);
                    } else if (empty($wallet)) {
                        return response()->json([
                            'message' => 'Account Wallet Not Found',
                            'status' => 403
                        ])->setStatusCode(403);
                    } else {
                        if ($request->credit == 'credit') {
                            $all_amount_credited = DB::table('deposit')->where(['credit_by' => $admin->username, 'status' => 1])->where('date', '>=', Carbon::now())->sum('amount');
                            if ($admin->type == 'CUSTOMER' && $request->amount > $this->core()->customer_amount) {
                                return response()->json([
                                    'status' => 403,
                                    'message' => 'Maximum Amount to Credit Users Daily is ₦' . number_format($this->core()->customer_amount, 2)
                                ])->setStatusCode(403);
                            } else if ($admin->type == 'CUSTOMER' && $all_amount_credited  > $this->core()->customer_amount) {
                                return response()->json([
                                    'status' => 403,
                                    'message' => 'Credit User Daily Amount Exhausted'
                                ])->setStatusCode(403);
                            } else if ($admin->type == 'CUSTOMER' && $all_amount_credited + $request->amount > $this->core()->customer_amount) {
                                return response()->json([
                                    'status' => 403,
                                    'message' => 'Daliy Amount Remaining To Credit A User is ₦' . number_format($this->core()->customer_amount - $all_amount_credited, 2)
                                ])->setStatusCode(403);
                            } else {
                                $deposit_ref = $this->generate_ref('Credit');
                                // crediting users here
                                if ($request->wallet == 'wallet') {
                                    //credit user wallet
                                    // now update user
                                    $update_data = [
                                        'bal' => $user_details->bal + $request->amount
                                    ];
                                    if ($this->updateData($update_data, 'user', ['id' => $user_details->id])) {
                                        // insert into message
                                        $message_data = [
                                            'username' => $user_details->username,
                                            'amount' => $request->amount,
                                            'message' => $request->reason,
                                            'oldbal' => $user_details->bal,
                                            'newbal' => $user_details->bal + $request->amount,
                                            'adex_date' => $this->system_date(),
                                            'plan_status' => 1,
                                            'transid' => $deposit_ref,
                                            'role' => 'credit'
                                        ];
                                        $this->inserting_data('message', $message_data);
                                        // inserting notif
                                        $notif_data = [
                                            'username' => $user_details->username,
                                            'message' => 'Your Account has been credited ₦' . $request->amount . ' by admin',
                                            'date' => $this->system_date(),
                                            'adex' => 0
                                        ];
                                        $this->inserting_data('notif', $notif_data);
                                        // inserting into deposit table
                                        $deposit_data = [
                                            'username' => $user_details->username,
                                            'amount' => $request->amount,
                                            'oldbal' => $user_details->bal,
                                            'newbal' => $user_details->bal + $request->amount,
                                            'wallet_type' => $wallet,
                                            'type' => 'Admin Funding',
                                            'credit_by' => $admin->username,
                                            'date' => $this->system_date(),
                                            'status' => 1,
                                            'transid' => $deposit_ref,
                                            'charges' => 0.0
                                        ];
                                        $this->inserting_data('deposit', $deposit_data);
                                        if ($request->isnotif == true) {
                                            //sending mail over here
                                            $email_data = [
                                                'name' => $user_details->name,
                                                'email' => $user_details->email,
                                                'username' => $user_details->username,
                                                'title' => 'Account Funding',
                                                'sender_mail' => $general->app_email,
                                                'app_name' => env('APP_NAME'),
                                                'wallet' => $wallet,
                                                'amount' => number_format($request->amount, 2),
                                                'oldbal' => number_format($user_details->bal, 2),
                                                'newbal' => number_format($user_details->bal + $request->amount, 2),
                                                'deposit_type' => strtoupper($request->credit),
                                                'transid' => $deposit_ref
                                            ];
                                            MailController::send_mail($email_data, 'email.deposit');
                                        }
                                        foreach ($all_admin as $adex) {
                                            $email_data = [
                                                'name' => $user_details->name,
                                                'email' => $adex->email,
                                                'username' => strtoupper($user_details->username),
                                                'title' => 'Account Funding',
                                                'sender_mail' => $general->app_email,
                                                'app_name' => env('APP_NAME'),
                                                'wallet' => $wallet,
                                                'amount' => number_format($request->amount, 2),
                                                'oldbal' => number_format($user_details->bal, 2),
                                                'newbal' => number_format($user_details->bal + $request->amount, 2),
                                                'deposit_type' => strtoupper($request->credit),
                                                'transid' => $deposit_ref,
                                                'credited_by' => strtoupper($admin->username)
                                            ];
                                            MailController::send_mail($email_data, 'email.admin');
                                        }
                                        return response()->json([
                                            'status' => 'success',
                                            'account_type' => $wallet,
                                            'message' => 'Account Credited SuccessFully'
                                        ]);
                                    } else {
                                        return response()->json([
                                            'message' => 'Unable to Credit User',
                                            'status' => 403
                                        ])->setStatusCode(403);
                                    }
                                } else {
                                    // funding the wallet funding (Stock Funding)
                                    $stock_user_wallet = DB::table('wallet_funding')->where('username', $request->user_username);
                                    if ($stock_user_wallet->count() == 1) {
                                        $user_stock_details = $stock_user_wallet->first();
                                        $ad = $request->wallet;
                                        $update_data = [
                                            $request->wallet => $user_stock_details->$ad + $request->amount
                                        ];
                                        if ($this->updateData($update_data, 'wallet_funding', ['id' => $user_stock_details->id])) {
                                            // insert into message
                                            $message_data = [
                                                'username' => $user_details->username,
                                                'amount' => $request->amount,
                                                'message' => $request->reason,
                                                'oldbal' => $user_stock_details->$ad,
                                                'newbal' => $user_stock_details->$ad + $request->amount,
                                                'adex_date' => $this->system_date(),
                                                'plan_status' => 1,
                                                'transid' => $deposit_ref,
                                                'role' => 'credit'
                                            ];
                                            $this->inserting_data('message', $message_data);
                                            // inserting notif
                                            $notif_data = [
                                                'username' => $user_details->username,
                                                'message' => 'Your Account has been credited ₦' . $request->amount . ' by admin',
                                                'date' => $this->system_date(),
                                                'adex' => 0
                                            ];
                                            $this->inserting_data('notif', $notif_data);
                                            // inserting into deposit table
                                            $deposit_data = [
                                                'username' => $user_details->username,
                                                'amount' => $request->amount,
                                                'oldbal' => $user_stock_details->$ad,
                                                'newbal' => $user_stock_details->$ad + $request->amount,
                                                'wallet_type' => $wallet,
                                                'type' => 'Admin Funding',
                                                'credit_by' => $admin->username,
                                                'date' => $this->system_date(),
                                                'status' => 1,
                                                'transid' => $deposit_ref,
                                                'charges' => 0.00
                                            ];
                                            $this->inserting_data('deposit', $deposit_data);
                                            if ($request->isnotif == true) {
                                                //sending mail over here
                                                $email_data = [
                                                    'name' => $user_details->name,
                                                    'email' => $user_details->email,
                                                    'username' => $user_details->username,
                                                    'title' => 'Account Funding',
                                                    'sender_mail' => $general->app_email,
                                                    'app_name' => env('APP_NAME'),
                                                    'wallet' => $wallet,
                                                    'amount' => number_format($request->amount, 2),
                                                    'oldbal' => number_format($user_stock_details->$ad, 2),
                                                    'newbal' => number_format($user_stock_details->$ad + $request->amount, 2),
                                                    'deposit_type' => strtoupper($request->credit),
                                                    'transid' => $deposit_ref
                                                ];
                                                MailController::send_mail($email_data, 'email.deposit');
                                            }
                                            foreach ($all_admin as $adex) {
                                                $email_data = [
                                                    'name' => $user_details->name,
                                                    'email' => $adex->email,
                                                    'username' => strtoupper($user_details->username),
                                                    'title' => 'Account Funding',
                                                    'sender_mail' => $general->app_email,
                                                    'app_name' => env('APP_NAME'),
                                                    'wallet' => $wallet,
                                                    'amount' => number_format($request->amount, 2),
                                                    'oldbal' => number_format($user_details->bal, 2),
                                                    'newbal' => number_format($user_details->bal + $request->amount, 2),
                                                    'deposit_type' => strtoupper($request->credit),
                                                    'transid' => $deposit_ref,
                                                    'credited_by' => strtoupper($admin->username)
                                                ];
                                                MailController::send_mail($email_data, 'email.admin');
                                            }
                                            return response()->json([
                                                'status' => 'success',
                                                'account_type' => $wallet,
                                                'message' => 'Account Credited SuccessFully'
                                            ]);
                                        } else {
                                            return response()->json([
                                                'status' => 403,
                                                'message' => 'Unable to Fund User Stock Wallet'
                                            ])->setStatusCode(403);
                                        }
                                    } else {
                                        return response()->json([
                                            'status' => 403,
                                            'message' => strtoupper($user_details->username) . ' has not login and is wallet funnding account has not been created'
                                        ])->setStatusCode(403);
                                    }
                                }
                            }
                        } else if ($request->credit == 'debit') {
                            $deposit_ref = $this->generate_ref('Debit');
                            // debiting user over here
                            if ($request->wallet == 'wallet') {
                                // debiting ain wallet
                                $update_data = [
                                    'bal' => $user_details->bal - $request->amount
                                ];
                                if ($this->updateData($update_data, 'user', ['id' => $user_details->id])) {
                                    // insert into message
                                    $message_data = [
                                        'username' => $user_details->username,
                                        'amount' => $request->amount,
                                        'message' => $request->reason,
                                        'oldbal' => $user_details->bal,
                                        'newbal' => $user_details->bal - $request->amount,
                                        'adex_date' => $this->system_date(),
                                        'plan_status' => 1,
                                        'transid' => $deposit_ref,
                                        'role' => 'debit'
                                    ];
                                    $this->inserting_data('message', $message_data);
                                    // inserting notif
                                    $notif_data = [
                                        'username' => $user_details->username,
                                        'message' => 'Your Account has been debited ₦' . $request->amount . ' by admin',
                                        'date' => $this->system_date(),
                                        'adex' => 0
                                    ];
                                    $this->inserting_data('notif', $notif_data);
                                    if ($request->isnotif == true) {
                                        //sending mail over here
                                        $email_data = [
                                            'name' => $user_details->name,
                                            'email' => $user_details->email,
                                            'username' => $user_details->username,
                                            'title' => 'Account Debited',
                                            'sender_mail' => $general->app_email,
                                            'app_name' => env('APP_NAME'),
                                            'wallet' => $wallet,
                                            'amount' => number_format($request->amount, 2),
                                            'oldbal' => number_format($user_details->bal, 2),
                                            'newbal' => number_format($user_details->bal - $request->amount, 2),
                                            'deposit_type' => strtoupper($request->credit),
                                            'transid' => $deposit_ref
                                        ];
                                        MailController::send_mail($email_data, 'email.deposit');
                                    }
                                    foreach ($all_admin as $adex) {
                                        $email_data = [
                                            'name' => $user_details->name,
                                            'email' => $adex->email,
                                            'username' => strtoupper($user_details->username),
                                            'title' => 'Account Funding',
                                            'sender_mail' => $general->app_email,
                                            'app_name' => env('APP_NAME'),
                                            'wallet' => $wallet,
                                            'amount' => number_format($request->amount, 2),
                                            'oldbal' => number_format($user_details->bal, 2),
                                            'newbal' => number_format($user_details->bal - $request->amount, 2),
                                            'deposit_type' => strtoupper($request->credit),
                                            'transid' => $deposit_ref,
                                            'credited_by' => strtoupper($admin->username)
                                        ];
                                        MailController::send_mail($email_data, 'email.admin');
                                    }
                                    return response()->json([
                                        'status' => 'success',
                                        'account_type' => $wallet,
                                        'message' => 'Account Debited SuccessFully'
                                    ]);
                                } else {
                                    return response()->json([
                                        'message' => 'Unable to Debit User',
                                        'status' => 403
                                    ])->setStatusCode(403);
                                }
                            } else {
                                // debiting stock wallet
                                $stock_user_wallet = DB::table('wallet_funding')->where('username', $request->user_username);
                                if ($stock_user_wallet->count() == 1) {
                                    $user_stock_details = $stock_user_wallet->first();
                                    $ad = $request->wallet;
                                    $update_data = [
                                        $request->wallet => $user_stock_details->$ad - $request->amount
                                    ];
                                    if ($this->updateData($update_data, 'wallet_funding', ['id' => $user_stock_details->id])) {
                                        // insert into message
                                        $message_data = [
                                            'username' => $user_details->username,
                                            'amount' => $request->amount,
                                            'message' => $request->reason,
                                            'oldbal' => $user_stock_details->$ad,
                                            'newbal' => $user_stock_details->$ad - $request->amount,
                                            'adex_date' => $this->system_date(),
                                            'plan_status' => 1,
                                            'transid' => $deposit_ref,
                                            'role' => 'debit'
                                        ];
                                        $this->inserting_data('message', $message_data);
                                        // inserting notif
                                        $notif_data = [
                                            'username' => $user_details->username,
                                            'message' => 'Your Account has been debited ₦' . $request->amount . ' by admin',
                                            'date' => $this->system_date(),
                                            'adex' => 0
                                        ];
                                        $this->inserting_data('notif', $notif_data);
                                        if ($request->isnotif == true) {
                                            //sending mail over here
                                            $email_data = [
                                                'name' => $user_details->name,
                                                'email' => $user_details->email,
                                                'username' => $user_details->username,
                                                'title' => 'Account Debited',
                                                'sender_mail' => $general->app_email,
                                                'app_name' => env('APP_NAME'),
                                                'wallet' => $wallet,
                                                'amount' => number_format($request->amount, 2),
                                                'oldbal' => number_format($user_stock_details->$ad, 2),
                                                'newbal' => number_format($user_stock_details->$ad - $request->amount, 2),
                                                'deposit_type' => strtoupper($request->credit),
                                                'transid' => $deposit_ref
                                            ];
                                            MailController::send_mail($email_data, 'email.deposit');
                                        }
                                        foreach ($all_admin as $adex) {
                                            $email_data = [
                                                'name' => $user_details->name,
                                                'email' => $adex->email,
                                                'username' => strtoupper($user_details->username),
                                                'title' => 'Account Funding',
                                                'sender_mail' => $general->app_email,
                                                'app_name' => env('APP_NAME'),
                                                'wallet' => $wallet,
                                                'amount' => number_format($request->amount, 2),
                                                'oldbal' => number_format($user_details->bal, 2),
                                                'newbal' => number_format($user_details->bal - $request->amount, 2),
                                                'deposit_type' => strtoupper($request->credit),
                                                'transid' => $deposit_ref,
                                                'credited_by' => strtoupper($admin->username)
                                            ];
                                            MailController::send_mail($email_data, 'email.admin');
                                        }
                                        return response()->json([
                                            'status' => 'success',
                                            'account_type' => $wallet,
                                            'message' => 'Account Debited SuccessFully'
                                        ]);
                                    } else {
                                        return response()->json([
                                            'status' => 403,
                                            'message' => 'Unable to Debit User Stock Wallet'
                                        ])->setStatusCode(403);
                                    }
                                } else {
                                    return response()->json([
                                        'status' => 403,
                                        'message' => strtoupper($user_details->username) . ' has not login and is wallet funnding account has not been created'
                                    ])->setStatusCode(403);
                                }
                            }
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Account Debit/Credit Unknown'
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
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function UpgradeUserAccount(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                $general = $this->general();
                $user = DB::table('user')->where('username', $request->user_username);
                $details = $user->first();
                if ($check_user->count() > 0) {
                    $validator =   validator::make($request->all(), [
                        'user_username' => 'required|string',
                        'role' => 'required|string',
                    ], [
                        'role.required' => 'Account Role Required',
                    ]);
                    if ($validator->fails()) {
                        return response()->json([
                            'message' => $validator->errors()->first(),
                            'status' => 403
                        ])->setStatusCode(403);
                    } else if ($user->count() != 1) {
                        return response()->json([
                            'message' => 'Unable to Get the Correspond User Username',
                            'status' => 403
                        ])->setStatusCode(403);
                    } else {
                        if ($this->updateData([
                            'type' => $request->role
                        ], 'user', ['id' => $details->id])) {
                            $dis = $this->generate_ref('Upgrade/Downgrade');
                            $message_data = [
                                'username' => $details->username,
                                'amount' => 0.00,
                                'message' => 'Your Acount Has Been Upgrade to ' . $request->role . ' Package',
                                'oldbal' => $details->bal,
                                'newbal' => $details->bal,
                                'adex_date' => $this->system_date(),
                                'plan_status' => 1,
                                'transid' => $dis,
                                'role' => 'upgrade'
                            ];
                            $this->inserting_data('message', $message_data);
                            if ($request->isnotif == true) {
                                //sending mail over here
                                $email_data = [
                                    'name' => $details->name,
                                    'email' => $details->email,
                                    'username' => $details->username,
                                    'title' => 'Account Upgrade/Downgrade',
                                    'sender_mail' => $general->app_email,
                                    'app_name' => env('APP_NAME'),
                                    'amount' => 0.00,
                                    'oldbal' => number_format($details->bal, 2),
                                    'newbal' => number_format($details->bal, 2),
                                    'deposit_type' => strtoupper($request->credit),
                                    'transid' => $dis,
                                    'role' => $request->role
                                ];
                                MailController::send_mail($email_data, 'email.upgrade');
                            }
                            // inserting notif
                            $notif_data = [
                                'username' => $details->username,
                                'message' => 'Your Acount Has Been Upgrade to ' . $request->role . ' Package',
                                'date' => $this->system_date(),
                                'adex' => 0
                            ];
                            $this->inserting_data('notif', $notif_data);
                            return response()->json([
                                'status' => 'success',
                                'message' => 'Acount Upgraded'
                            ]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Unable to upgrade user'
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
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function ResetUserPassword(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                $general = $this->general();
                $user = DB::table('user')->where('username', $request->user_username);
                $details = $user->first();
                if ($check_user->count() > 0) {
                    $validator =   validator::make($request->all(), [
                        'user_username' => 'required|string',
                        'password' => 'required|string|min:8',
                    ]);
                    if ($validator->fails()) {
                        return response()->json([
                            'message' => $validator->errors()->first(),
                            'status' => 403
                        ])->setStatusCode(403);
                    } else if ($user->count() != 1) {
                        return response()->json([
                            'message' => 'Unable to Get the Correspond User Username',
                            'status' => 403
                        ])->setStatusCode(403);
                    } else {
                        if ($this->updateData([
                            'password' => password_hash($request->password,  PASSWORD_DEFAULT, array('cost' => 16)),
                        ], 'user', ['id' => $details->id])) {
                            if ($request->isnotif == true) {
                                //sending mail over here
                                $email_data = [
                                    'name' => $details->name,
                                    'email' => $details->email,
                                    'username' => $details->username,
                                    'title' => 'Password Reset',
                                    'sender_mail' => $general->app_email,
                                    'app_name' => env('APP_NAME'),
                                    'password' => $request->password,
                                    'username' => $details->username
                                ];
                                MailController::send_mail($email_data, 'email.admin_reset');
                            }
                            return response()->json([
                                'status' => 'success',
                                'message' => 'Account Password Reseted'
                            ]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Unable to Reset User Password'
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
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function Automated(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() == 1) {
                    if (isset($request->username)) {
                        for ($i = 0; $i < count($request->username); $i++) {
                            $username = $request->username[$i];
                            $delete_user = DB::table('user')->where('username',  $username);
                            $user_id = $delete_user->first();
                            $id = $user_id->id;
                            if ($delete_user->count() > 0) {
                                $data = [
                                    'autofund' => null,
                                    'wema' => null,
                                    'rolex' => null,
                                    'sterlen' => null,
                                    'fed' => null
                                ];
                                $delete = $this->updateData($data, 'user', ['id' => $id]);
                            } else {
                                $delete = false;
                            }
                        }
                        if ($delete) {
                            return response()->json([
                                'status' => 'success',
                                'message' => 'Account Details Deleted Successfully'
                            ]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Unable To delete Account'
                            ])->setStatusCode(403);
                        }
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'User ID  Required'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function BankDetails(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() == 1) {
                    if (isset($request->username)) {
                        for ($i = 0; $i < count($request->username); $i++) {
                            $username = $request->username[$i];
                            $delete_user = DB::table('user')->where('username',  $username);
                            $user_id = $delete_user->first();
                            $id = $user_id->username;
                            if ($delete_user->count() > 0) {
                                $delete = DB::table('user_bank')->where('username', $id)->delete();
                            } else {
                                $delete = false;
                            }
                        }
                        if ($delete) {
                            return response()->json([
                                'status' => 'success',
                                'message' => 'Account Details Deleted Successfully'
                            ]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Unable To delete Account'
                            ])->setStatusCode(403);
                        }
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'User ID  Required'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function AddBlock(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                $admin = $check_user->first();
                if ($check_user->count() == 1) {
                    if (!empty($request->number)) {
                        if (DB::table('block')->where('number', $request->number)->count() == 0) {
                            if ($this->inserting_data('block', ['number' => $request->number, 'date' => $this->system_date(), 'added_by' => $admin->username])) {
                                return response()->json([
                                    'status' => 'success'
                                ]);
                            } else {
                                return response()->json([
                                    'status' => 403,
                                    'message' => 'Unable to Add Block Number'
                                ])->setStatusCode(403);
                            }
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Block Number Added Already'
                            ])->setStatusCode(403);
                        }
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Block Number Required'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function DeleteBlock(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() == 1) {
                    if (isset($request->number)) {
                        for ($i = 0; $i < count($request->number); $i++) {
                            $username = $request->number[$i];
                            $delete_user = DB::table('block')->where('number',  $username);
                            $user_id = $delete_user->first();
                            $id = $user_id->id;
                            if ($delete_user->count() > 0) {
                                $delete = DB::table('block')->where('id', $id)->delete();
                            } else {
                                $delete = false;
                            }
                        }
                        if ($delete) {
                            return response()->json([
                                'status' => 'success',
                                'message' => 'Blocked Number Deleted Successfully'
                            ]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Unable To delete Account'
                            ])->setStatusCode(403);
                        }
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Block Id Required'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function Discount(Request $request)
    {
        try {
            if ($this->validateOrigin($request)) {
                if (!empty($request->id)) {
                    $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                        $query->where('type', 'ADMIN');
                    });
                    $database_name = null;
                    if ($check_user->count() == 1) {
                        if (isset($request->database_name)) {
                            $database_name = $request->database_name;
                            $search = strtolower($request->search);
                        }

                        if ($database_name == 'wallet_funding') {
                            if (!empty($search)) {
                                return response()->json([
                                    'all_stock' => DB::table('wallet_funding')->where(function ($query) use ($search) {
                                        $query->orWhere('username', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'all_stock' => DB::table('wallet_funding')->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        } else {
                            return response()->json([
                                'airtime_discount' => DB::table('airtime_discount')->first(),
                                'cable_charges' => DB::table('cable_charge')->first(),
                                'bill_charges' => DB::table('bill_charge')->first(),
                                'cash_discount' => DB::table('cash_discount')->first(),
                                'result_charges' => DB::table('result_charge')->first(),
                                'all_network' => DB::table('network')->get(),
                                'cable_result_lock' => DB::table('cable_result_lock')->first(),

                                'adex_api'  => DB::table('adex_api')->first(),
                                'msorg_api' => DB::table('msorg_api')->first(),
                                'virus_api' => DB::table('virus_api')->first(),
                                'other_api' => DB::table('other_api')->first(),
                                'web_api' => DB::table('web_api')->first(),
                                'airtime_sel' => DB::table('airtime_sel')->first(),
                                'bill_sel' => DB::table('bill_sel')->first(),
                                'cable_sel' => DB::table('cable_sel')->first(),
                                'bulksms_sel' => DB::table('bulksms_sel')->first(),
                                'exam_sel' => DB::table('exam_sel')->first(),
                            ]);
                        }
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Not Authorised'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'status' => 'fail',
                        'message' => 'User ID required'
                    ], 403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Origin not allowed'
                ], 403);
            }
        } catch (\Exception $e) {
            \Log::error('Discount method error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_id' => $request->id ?? 'none'
            ]);
            return response()->json([
                'status' => 'fail',
                'message' => 'An error occurred while fetching data'
            ], 500);
        }
    }
    public function AirtimeDiscount(Request $request)
    {
        $allowedOrigins = array_filter(array_map('trim', explode(',', config('adex.app_key', ''))));
        $origin = $request->headers->get('origin');
        $originNormalized = rtrim($origin ?: '', '/');
        
        if (in_array($originNormalized, $allowedOrigins) || config('adex.device_key') === $request->header('Authorization')) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() == 1) {
                    $main_validator = validator::make($request->all(), [
                        'mtn_vtu_smart' => 'required|numeric|between:0,100',
                        'mtn_vtu_agent' => 'required|numeric|between:0,100',
                        'mtn_vtu_awuf' => 'required|numeric|between:0,100',
                        'mtn_vtu_api' => 'required|numeric|between:0,100',
                        'mtn_vtu_special' => 'required|numeric|between:0,100',
                        // airtel vtu
                        'airtel_vtu_smart' => 'required|numeric|between:0,100',
                        'airtel_vtu_agent' => 'required|numeric|between:0,100',
                        'airtel_vtu_awuf' => 'required|numeric|between:0,100',
                        'airtel_vtu_api' => 'required|numeric|between:0,100',
                        'airtel_vtu_special' => 'required|numeric|between:0,100',
                        //  glo vtu
                        'glo_vtu_smart' => 'required|numeric|between:0,100',
                        'glo_vtu_agent' => 'required|numeric|between:0,100',
                        'glo_vtu_awuf' => 'required|numeric|between:0,100',
                        'glo_vtu_api' => 'required|numeric|between:0,100',
                        'glo_vtu_special' => 'required|numeric|between:0,100',
                        // 9mobile
                        'mobile_vtu_smart' => 'required|numeric|between:0,100',
                        'mobile_vtu_agent' => 'required|numeric|between:0,100',
                        'mobile_vtu_awuf' => 'required|numeric|between:0,100',
                        'mobile_vtu_api' => 'required|numeric|between:0,100',
                        'mobile_vtu_special' => 'required|numeric|between:0,100',

                        // mtn share and sell
                        'mtn_share_smart' => 'required|numeric|between:0,100',
                        'mtn_share_agent' => 'required|numeric|between:0,100',
                        'mtn_share_awuf' => 'required|numeric|between:0,100',
                        'mtn_share_api' => 'required|numeric|between:0,100',
                        'mtn_share_special' => 'required|numeric|between:0,100',
                        // airtel share and sell
                        'airtel_share_smart' => 'required|numeric|between:0,100',
                        'airtel_share_agent' => 'required|numeric|between:0,100',
                        'airtel_share_awuf' => 'required|numeric|between:0,100',
                        'airtel_share_api' => 'required|numeric|between:0,100',
                        'airtel_share_special' => 'required|numeric|between:0,100',
                        //  glo share and sell
                        'glo_share_smart' => 'required|numeric|between:0,100',
                        'glo_share_agent' => 'required|numeric|between:0,100',
                        'glo_share_awuf' => 'required|numeric|between:0,100',
                        'glo_share_api' => 'required|numeric|between:0,100',
                        'glo_share_special' => 'required|numeric|between:0,100',
                        // 9mobile share and sell
                        'mobile_share_smart' => 'required|numeric|between:0,100',
                        'mobile_share_agent' => 'required|numeric|between:0,100',
                        'mobile_share_awuf' => 'required|numeric|between:0,100',
                        'mobile_share_api' => 'required|numeric|between:0,100',
                        'mobile_share_special' => 'required|numeric|between:0,100',

                        // min and max
                        'min_airtime' => 'required|numeric|integer|not_in:0|gt:0',
                        'max_airtime' => 'required|numeric|integer|not_in:0|gt:0'
                    ]);
                    if ($main_validator->fails()) {
                        return response()->json([
                            'message' => $main_validator->errors()->first(),
                            'status' => 403
                        ])->setStatusCode(403);
                    } else {
                        $data = [
                            'mtn_vtu_smart' => $request->mtn_vtu_smart,
                            'mtn_vtu_awuf' => $request->mtn_vtu_awuf,
                            'mtn_vtu_agent' => $request->mtn_vtu_agent,
                            'mtn_vtu_api' => $request->mtn_vtu_api,
                            'mtn_vtu_special' => $request->mtn_vtu_special,
                            // airtel vtu
                            'airtel_vtu_smart' => $request->airtel_vtu_smart,
                            'airtel_vtu_awuf' => $request->airtel_vtu_awuf,
                            'airtel_vtu_agent' => $request->airtel_vtu_agent,
                            'airtel_vtu_api' => $request->airtel_vtu_api,
                            'airtel_vtu_special' => $request->airtel_vtu_special,

                            // glo vtu
                            'glo_vtu_smart' => $request->glo_vtu_smart,
                            'glo_vtu_awuf' => $request->glo_vtu_awuf,
                            'glo_vtu_agent' => $request->glo_vtu_agent,
                            'glo_vtu_api' => $request->glo_vtu_api,
                            'glo_vtu_special' => $request->glo_vtu_special,

                            // 9mobile vtu
                            'mobile_vtu_smart' => $request->mobile_vtu_smart,
                            'mobile_vtu_awuf' => $request->mobile_vtu_awuf,
                            'mobile_vtu_agent' => $request->mobile_vtu_agent,
                            'mobile_vtu_api' => $request->mobile_vtu_api,
                            'mobile_vtu_special' => $request->mobile_vtu_special,

                            // mtn share and sell

                            'mtn_share_smart' => $request->mtn_share_smart,
                            'mtn_share_awuf' => $request->mtn_share_awuf,
                            'mtn_share_agent' => $request->mtn_share_agent,
                            'mtn_share_api' => $request->mtn_share_api,
                            'mtn_share_special' => $request->mtn_share_special,
                            // airtel share ad sell
                            'airtel_share_smart' => $request->airtel_share_smart,
                            'airtel_share_awuf' => $request->airtel_share_awuf,
                            'airtel_share_agent' => $request->airtel_share_agent,
                            'airtel_share_api' => $request->airtel_share_api,
                            'airtel_share_special' => $request->airtel_share_special,

                            // glo share and sell
                            'glo_share_smart' => $request->glo_share_smart,
                            'glo_share_awuf' => $request->glo_share_awuf,
                            'glo_share_agent' => $request->glo_share_agent,
                            'glo_share_api' => $request->glo_share_api,
                            'glo_share_special' => $request->glo_share_special,

                            // 9mobile share and sell
                            'mobile_share_smart' => $request->mobile_share_smart,
                            'mobile_share_awuf' => $request->mobile_share_awuf,
                            'mobile_share_agent' => $request->mobile_share_agent,
                            'mobile_share_api' => $request->mobile_share_api,
                            'mobile_share_special' => $request->mobile_share_special,

                            // max and min

                            'max_airtime' => $request->max_airtime,
                            'min_airtime' => $request->min_airtime
                        ];
                        if (DB::table('airtime_discount')->update($data)) {
                            return response()->json([
                                'status' => 'success',
                                'message' => 'Updated Successfully'
                            ]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Unable To Update Airtime Discount'
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
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function CableCharges(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() == 1) {
                    if ($request->direct == true || $request->direct == 1) {
                        $main_validator = validator::make($request->all(), [
                            'dstv' => 'required|numeric|integer|not_in:0|gt:0',
                            'gotv' => 'required|numeric|integer|not_in:0|gt:0',
                            'startime' => 'required|numeric|integer|not_in:0|gt:0',
                        ]);
                        if ($main_validator->fails()) {
                            return response()->json([
                                'message' => $main_validator->errors()->first(),
                                'status' => 403
                            ])->setStatusCode(403);
                        } else {
                            $data = [
                                'dstv' => $request->dstv,
                                'gotv' => $request->gotv,
                                'startime' => $request->startime,
                                'direct' => 1
                            ];
                            if (DB::table('cable_charge')->update($data)) {
                                return response()->json([
                                    'status' => 'success',
                                    'message' => 'Updated Successfully'
                                ]);
                            } else {
                                return response()->json([
                                    'status' => 403,
                                    'message' => 'Unable To Update Cable Charges'
                                ])->setStatusCode(403);
                            }
                        }
                    } else {
                        $main_validator = validator::make($request->all(), [
                            'dstv' => 'required|numeric|between:0,100',
                            'gotv' => 'required|numeric|between:0,100',
                            'startime' => 'required|numeric|between:0,100',
                        ], [
                            'dstv.between' => 'DSTV Charges Must Be Between 0 and 100 (charging in percentage)',
                            'gotv.between' => 'GOTV Charges Must Be Between 0 and 100 (charging in percentage)',
                            'startime.between' => 'STARTIME Charges Must Be Between 0 and 100 (charging in percentage)'
                        ]);

                        if ($main_validator->fails()) {
                            return response()->json([
                                'message' => $main_validator->errors()->first(),
                                'status' => 403
                            ])->setStatusCode(403);
                        } else {
                            $data = [
                                'dstv' => $request->dstv,
                                'gotv' => $request->gotv,
                                'startime' => $request->startime,
                                'direct' => 0
                            ];
                            if (DB::table('cable_charge')->update($data)) {
                                return response()->json([
                                    'status' => 'success',
                                    'message' => 'Updated Successfully'
                                ]);
                            } else {
                                return response()->json([
                                    'status' => 403,
                                    'message' => 'Unable To Update Cable Charges'
                                ])->setStatusCode(403);
                            }
                        }
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function BillCharges(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() == 1) {
                    if ($request->direct == true || $request->direct == 1) {
                        $main_validator = validator::make($request->all(), [
                            'bill' => 'required|numeric|integer|not_in:0|gt:0',
                            'bill_max' =>  'required|numeric|integer|not_in:0|gt:0',
                            'bill_min' =>  'required|numeric|integer|not_in:0|gt:0',
                        ]);
                        if ($main_validator->fails()) {
                            return response()->json([
                                'message' => $main_validator->errors()->first(),
                                'status' => 403
                            ])->setStatusCode(403);
                        } else {
                            $data = [
                                'bill' => $request->bill,
                                'bill_max' => $request->bill_max,
                                'bill_min' => $request->bill_min,
                                'direct' => 1
                            ];
                            if (DB::table('bill_charge')->update($data)) {
                                return response()->json([
                                    'status' => 'success',
                                    'message' => 'Updated Successfully'
                                ]);
                            } else {
                                return response()->json([
                                    'status' => 403,
                                    'message' => 'Unable To Update Bill Charges'
                                ])->setStatusCode(403);
                            }
                        }
                    } else {
                        $main_validator = validator::make($request->all(), [
                            'bill' => 'required|numeric|between:0,100',
                            'bill_max' =>  'required|numeric|integer|not_in:0|gt:0',
                            'bill_min' =>  'required|numeric|integer|not_in:0|gt:0',
                        ], [
                            'bill.between' => 'Bill Charges Must Be Between 0 and 100 (charging in percentage)'
                        ]);

                        if ($main_validator->fails()) {
                            return response()->json([
                                'message' => $main_validator->errors()->first(),
                                'status' => 403
                            ])->setStatusCode(403);
                        } else {
                            $data = [
                                'bill' => $request->bill,
                                'bill_max' => $request->bill_max,
                                'bill_min' => $request->bill_min,
                                'direct' => 0
                            ];
                            if (DB::table('bill_charge')->update($data)) {
                                return response()->json([
                                    'status' => 'success',
                                    'message' => 'Updated Successfully'
                                ]);
                            } else {
                                return response()->json([
                                    'status' => 403,
                                    'message' => 'Unable To Update Bill Charges'
                                ])->setStatusCode(403);
                            }
                        }
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function CashDiscount(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() == 1) {

                    $main_validator = validator::make($request->all(), [
                        'mtn_number' => 'required|numeric|digits:11',
                        'airtel_number' => 'required|numeric|digits:11',
                        'glo_number' => 'required|numeric|digits:11',
                        'mobile_number' => 'required|numeric|digits:11',
                        'mtn' => 'required|numeric|between:0,100',
                        'airtel' => 'required|numeric|between:0,100',
                        'glo' => 'required|numeric|between:0,100',
                        'mobile' => 'required|numeric|between:0,100'
                    ]);
                    if ($main_validator->fails()) {
                        return response()->json([
                            'message' => $main_validator->errors()->first(),
                            'status' => 403
                        ])->setStatusCode(403);
                    } else {
                        $data = [
                            'mtn' => $request->mtn,
                            'glo' => $request->glo,
                            'airtel' => $request->airtel,
                            'mobile' => $request->mobile,
                            'mtn_number' => $request->mtn_number,
                            'glo_number' => $request->glo_number,
                            'airtel_number' => $request->airtel_number,
                            'mobile_number' => $request->mobile_number,
                        ];
                        if (DB::table('cash_discount')->update($data)) {
                            return response()->json([
                                'status' => 'success',
                                'message' => 'Updated Successfully'
                            ]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Unable To Update '
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
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function ResultCharge(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() == 1) {

                    $main_validator = validator::make($request->all(), [
                        'waec' => 'required|numeric|integer|not_in:0|gt:0',
                        'neco' => 'required|numeric|integer|not_in:0|gt:0',
                        'nabteb' => 'required|numeric|integer|not_in:0|gt:0',
                    ]);
                    if ($main_validator->fails()) {
                        return response()->json([
                            'message' => $main_validator->errors()->first(),
                            'status' => 403
                        ])->setStatusCode(403);
                    } else {
                        $data = [
                            'waec' => $request->waec,
                            'neco' => $request->neco,
                            'nabteb' => $request->nabteb,
                        ];
                        if (DB::table('result_charge')->update($data)) {
                            return response()->json([
                                'status' => 'success',
                                'message' => 'Updated Successfully'
                            ]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Unable To Update result Charges'
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
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function OtherCharge(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() == 1) {

                    $main_validator = validator::make($request->all(), [
                        'bulk_sms' => 'required|numeric|integer|not_in:0|gt:0',
                        'bulk_length' => 'required|numeric|integer|not_in:0|gt:0',
                        'affliate_price' => 'required|numeric|integer|not_in:0|gt:0',
                        'awuf_price' => 'required|numeric|integer|not_in:0|gt:0',
                        'agent_price' => 'required|numeric|integer|not_in:0|gt:0',
                        'monnify_charge' => 'required|numeric|between:0,100',
                        'earning_min' => 'required|numeric|integer|not_in:0|gt:0',
                        'customer_amount' => 'required|numeric|integer|not_in:0|gt:0',
                    ]);
                    if ($main_validator->fails()) {
                        return response()->json([
                            'message' => $main_validator->errors()->first(),
                            'status' => 403
                        ])->setStatusCode(403);
                    } else {
                        $data = [
                            'bulk_sms' => $request->bulk_sms,
                            'bulk_length' => $request->bulk_length,
                            'affliate_price' => $request->affliate_price,
                            'awuf_price' => $request->awuf_price,
                            'agent_price' => $request->agent_price,
                            'monnify_charge' => $request->monnify_charge,
                            'earning_min' => $request->earning_min,
                            'customer_amount' => $request->customer_amount
                        ];
                        if (DB::table('settings')->update($data)) {
                            return response()->json([
                                'status' => 'success',
                                'message' => 'Updated Successfully'
                            ]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Unable To Update Other Charges'
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
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function RechargeCardSel(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() == 1) {
                    $main_validator = validator::make($request->all(), [
                        'mtn' => 'required',
                        'airtel' => 'required',
                        'glo' => 'required',
                        'mobile' => 'required',
                    ]);
                    if ($main_validator->fails()) {
                        return response()->json([
                            'message' => $main_validator->errors()->first(),
                            'status' => 403
                        ])->setStatusCode(403);
                    } else {
                        $data = [
                            'mtn' => $request->mtn,
                            'airtel' => $request->airtel,
                            'glo' => $request->glo,
                            'mobile' => $request->mobile,
                        ];
                        DB::table('recharge_card_sel')->update($data);
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Updated Success'
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function DataCardSel(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() == 1) {
                    $main_validator = validator::make($request->all(), [
                        'mtn' => 'required',
                        'airtel' => 'required',
                        'glo' => 'required',
                        'mobile' => 'required',
                    ]);
                    if ($main_validator->fails()) {
                        return response()->json([
                            'message' => $main_validator->errors()->first(),
                            'status' => 403
                        ])->setStatusCode(403);
                    } else {
                        $data = [
                            'mtn' => $request->mtn,
                            'airtel' => $request->airtel,
                            'glo' => $request->glo,
                            'mobile' => $request->mobile,
                        ];
                        DB::table('data_card_sel')->update($data);
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Updated Success'
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function DataSel(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() == 1) {
                    $main_validator = validator::make($request->all(), [
                        'mtn_sme' => 'required',
                        'airtel_sme' => 'required',
                        'glo_sme' => 'required',
                        'mobile_sme' => 'required',
                        'mtn_cg' => 'required',
                        'airtel_cg' => 'required',
                        'glo_cg' => 'required',
                        'mobile_cg' => 'required',
                        'mtn_g' => 'required',
                        'airtel_g' => 'required',
                        'glo_g' => 'required',
                        'mobile_g' => 'required'
                    ]);
                    if ($main_validator->fails()) {
                        return response()->json([
                            'message' => $main_validator->errors()->first(),
                            'status' => 403
                        ])->setStatusCode(403);
                    } else {
                        $data = [
                            'mtn_sme' => $request->mtn_sme,
                            'airtel_sme' => $request->airtel_sme,
                            'glo_sme' => $request->glo_sme,
                            'mobile_sme' => $request->mobile_sme,

                            'mtn_cg' => $request->mtn_cg,
                            'airtel_cg' => $request->airtel_cg,
                            'glo_cg'   => $request->glo_cg,
                            'mobile_cg' => $request->mobile_cg,

                            'mtn_g' => $request->mtn_g,
                            'airtel_g' => $request->airtel_g,
                            'glo_g' => $request->glo_g,
                            'mobile_g' => $request->mobile_g
                        ];
                        DB::table('data_sel')->update($data);
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Updated Success'
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function AirtimeSel(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() == 1) {
                    $main_validator = validator::make($request->all(), [
                        'mtn_vtu' => 'required',
                        'airtel_vtu' => 'required',
                        'glo_vtu' => 'required',
                        'mobile_vtu' => 'required',
                        'mtn_share' => 'required',
                        'airtel_share' => 'required',
                        'glo_share' => 'required',
                        'mobile_share' => 'required',
                    ]);
                    if ($main_validator->fails()) {
                        return response()->json([
                            'message' => $main_validator->errors()->first(),
                            'status' => 403
                        ])->setStatusCode(403);
                    } else {
                        $data = [
                            'mtn_vtu' => $request->mtn_vtu,
                            'airtel_vtu' => $request->airtel_vtu,
                            'glo_vtu' => $request->glo_vtu,
                            'mobile_vtu' => $request->mobile_vtu,

                            'mtn_share' => $request->mtn_share,
                            'airtel_share' => $request->airtel_share,
                            'glo_share'   => $request->glo_share,
                            'mobile_share' => $request->mobile_share,
                        ];
                        DB::table('airtime_sel')->update($data);
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Updated Success'
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function CableSel(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() == 1) {
                    $main_validator = validator::make($request->all(), [
                        'dstv' => 'required',
                        'startime' => 'required',
                        'gotv' => 'required',
                    ]);
                    if ($main_validator->fails()) {
                        return response()->json([
                            'message' => $main_validator->errors()->first(),
                            'status' => 403
                        ])->setStatusCode(403);
                    } else {
                        $data = [
                            'startime' => $request->startime,
                            'gotv' => $request->gotv,
                            'dstv' => $request->dstv,
                        ];
                        DB::table('cable_sel')->update($data);
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Updated Success'
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function BillSel(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() == 1) {
                    $main_validator = validator::make($request->all(), [
                        'bill' => 'required'
                    ]);
                    if ($main_validator->fails()) {
                        return response()->json([
                            'message' => $main_validator->errors()->first(),
                            'status' => 403
                        ])->setStatusCode(403);
                    } else {
                        $data = [
                            'bill' => $request->bill,
                        ];
                        DB::table('bill_sel')->update($data);
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Updated Success'
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function BulkSMSsel(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() == 1) {
                    $main_validator = validator::make($request->all(), [
                        'bulksms' => 'required'
                    ]);
                    if ($main_validator->fails()) {
                        return response()->json([
                            'message' => $main_validator->errors()->first(),
                            'status' => 403
                        ])->setStatusCode(403);
                    } else {
                        $data = [
                            'bulksms' => $request->bulksms,
                        ];
                        DB::table('bulksms_sel')->update($data);
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Updated Success'
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function ExamSel(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() == 1) {
                    $main_validator = validator::make($request->all(), [
                        'waec' => 'required',
                        'neco' => 'required',
                        'nabteb' => 'required',
                    ]);
                    if ($main_validator->fails()) {
                        return response()->json([
                            'message' => $main_validator->errors()->first(),
                            'status' => 403
                        ])->setStatusCode(403);
                    } else {
                        $data = [
                            'waec' => $request->waec,
                            'neco' => $request->neco,
                            'nabteb' => $request->nabteb,
                        ];
                        DB::table('exam_sel')->update($data);
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Updated Success'
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function AllUsersInfo(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    $search = strtolower($request->search);
                    if ($request->role == 'ALL' && $request->status == 'ALL' && empty($search)) {
                        return response()->json([
                            'all_users' => DB::table('user')->select('id', 'name', 'username', 'email', 'pin', 'phone', 'bal', 'refbal', 'kyc', 'status', 'type', 'profile_image', 'date')->orderBy('id', 'desc')->paginate($request->adex),
                        ]);
                    } else if ($request->role != 'ALL' && $request->status == 'ALL' && empty($search)) {
                        return response()->json([
                            'all_users' => DB::table('user')->where(['type' => $request->role])->select('id', 'name', 'username', 'email', 'pin', 'phone', 'bal', 'refbal', 'kyc', 'status', 'type', 'profile_image', 'date')->orderBy('id', 'desc')->paginate($request->adex),
                        ]);
                    } else if ($request->role == 'ALL' && $request->status != 'ALL' && empty($search)) {
                        return response()->json([
                            'all_users' => DB::table('user')->where(['status' => $request->status])->select('id', 'name', 'username', 'email', 'pin', 'phone', 'bal', 'refbal', 'kyc', 'status', 'type', 'profile_image', 'date')->orderBy('id', 'desc')->paginate($request->adex),
                        ]);
                    } else if ($request->role != 'ALL' && $request->status != 'ALL' && empty($search)) {
                        return response()->json([
                            'all_users' => DB::table('user')->where(['status' => $request->status, 'type' => $request->role])->select('id', 'name', 'username', 'email', 'pin', 'phone', 'bal', 'refbal', 'kyc', 'status', 'type', 'profile_image', 'date')->orderBy('id', 'desc')->paginate($request->adex),
                        ]);
                    } else if ($request->role == 'ALL' && $request->status == 'ALL' && !empty($search)) {
                        return response()->json([
                            'all_users' => DB::table('user')->where(function ($query) use ($search) {
                                $query->orWhere('username', 'LIKE', "%$search%")->orWhere('name', 'LIKE', "%$search%")->orWhere('email', 'LIKE', "%$search%")->orWhere('date', 'LIKE', "%$search%")->orWhere('phone', 'LIKE', "%$search%")->orWhere('pin', 'LIKE', "%$search%")->orWhere('type', 'LIKE', "%$search%");
                            })->select('id', 'name', 'username', 'email', 'pin', 'phone', 'bal', 'refbal', 'kyc', 'status', 'type', 'profile_image', 'date')->orderBy('id', 'desc')->paginate($request->adex),
                        ]);
                    } else if ($request->role != 'ALL' && $request->status == 'ALL' && !empty($search)) {
                        return response()->json([
                            'all_users' => DB::table('user')->where(['type' => $request->role])->where(function ($query) use ($search) {
                                $query->orWhere('username', 'LIKE', "%$search%")->orWhere('name', 'LIKE', "%$search%")->orWhere('email', 'LIKE', "%$search%")->orWhere('date', 'LIKE', "%$search%")->orWhere('phone', 'LIKE', "%$search%")->orWhere('pin', 'LIKE', "%$search%")->orWhere('type', 'LIKE', "%$search%");
                            })->select('id', 'name', 'username', 'email', 'pin', 'phone', 'bal', 'refbal', 'kyc', 'status', 'type', 'profile_image', 'date')->orderBy('id', 'desc')->paginate($request->adex),
                        ]);
                    } else if ($request->role == 'ALL' && $request->status != 'ALL' && !empty($search)) {
                        return response()->json([
                            'all_users' => DB::table('user')->where(['status' => $request->status])->where(function ($query) use ($search) {
                                $query->orWhere('username', 'LIKE', "%$search%")->orWhere('name', 'LIKE', "%$search%")->orWhere('email', 'LIKE', "%$search%")->orWhere('date', 'LIKE', "%$search%")->orWhere('phone', 'LIKE', "%$search%")->orWhere('pin', 'LIKE', "%$search%")->orWhere('type', 'LIKE', "%$search%");
                            })->select('id', 'name', 'username', 'email', 'pin', 'phone', 'bal', 'refbal', 'kyc', 'status', 'type', 'profile_image', 'date')->orderBy('id', 'desc')->paginate($request->adex),
                        ]);
                    } else if ($request->role != 'ALL' && $request->status != 'ALL' && !empty($search)) {
                        return response()->json([
                            'all_users' => DB::table('user')->where(['status' => $request->status, 'type' => $request->role])->where(function ($query) use ($search) {
                                $query->orWhere('username', 'LIKE', "%$search%")->orWhere('name', 'LIKE', "%$search%")->orWhere('email', 'LIKE', "%$search%")->orWhere('date', 'LIKE', "%$search%")->orWhere('phone', 'LIKE', "%$search%")->orWhere('pin', 'LIKE', "%$search%")->orWhere('type', 'LIKE', "%$search%");
                            })->select('id', 'name', 'username', 'email', 'pin', 'phone', 'bal', 'refbal', 'kyc', 'status', 'type', 'profile_image', 'date')->orderBy('id', 'desc')->paginate($request->adex),
                        ]);
                    } else {
                        return response()->json([
                            'all_users' => DB::table('user')->select('id', 'name', 'username', 'email', 'pin', 'phone', 'bal', 'refbal', 'kyc', 'status', 'type', 'profile_image', 'date')->orderBy('id', 'desc')->paginate($request->adex),
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function AllBankDetails(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    $search = strtolower($request->search);
                    if (!empty($search)) {
                        return response()->json([
                            'autobank' => DB::table('user')->where('autofund', 'ACTIVE')->select('id', 'username', 'profile_image', 'wema', 'rolex', 'sterlen', 'fed', 'bal', 'refbal', 'status')->orderBy('id', 'desc')->where(function ($query) use ($search) {
                                $query->orWhere('username', 'LIKE', "%$search%")->orWhere('name', 'LIKE', "%$search%")->orWhere('email', 'LIKE', "%$search%")->orWhere('date', 'LIKE', "%$search%")->orWhere('phone', 'LIKE', "%$search%")->orWhere('pin', 'LIKE', "%$search%")->orWhere('type', 'LIKE', "%$search%");
                            })->paginate($request->adex),
                        ]);
                    } else {
                        return response()->json([
                            'autobank' => DB::table('user')->where('autofund', 'ACTIVE')->select('id', 'username', 'profile_image', 'wema', 'rolex', 'sterlen', 'fed', 'bal', 'refbal', 'status')->orderBy('id', 'desc')->paginate($request->adex),
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function UserBankAccountD(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    $search = strtolower($request->search);
                    if (!empty($search)) {
                        return response()->json([
                            'userbank' =>  DB::table('user_bank')->where(function ($query) use ($search) {
                                $query->orWhere('username', 'LIKE', "%$search%");
                            })->orderBy('id', 'desc')->paginate($request->adex)
                        ]);
                    } else {
                        return response()->json([
                            'userbank' =>  DB::table('user_bank')->orderBy('id', 'desc')->paginate($request->adex)
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function AllUserBanned(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    return response()->json([
                        'autobanned' => DB::table('block')->leftJoin("user", function ($join) {
                            $join->on("user.username", "=", "block.added_by");
                        })->orderBy('block.id', 'desc')->get(),
                    ]);
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }
    public function AllSystemPlan(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    return response()->json([
                        'data_plans' => DB::table('data_plan')->leftJoin("user", function ($join) {
                            $join->on("user.username", "=", "data_plan.added_by");
                        })->orderBy('data_plan.id', 'desc')->get(),
                        'cable_plans' =>  DB::table('cable_plan')->leftJoin("user", function ($join) {
                            $join->on("user.username", "=", "cable_plan.added_by");
                        })->orderBy('cable_plan.id', 'desc')->get(),
                        'bill_plans' => DB::table('bill_plan')->leftJoin("user", function ($join) {
                            $join->on("user.username", "=", "bill_plan.added_by");
                        })->orderBy('bill_plan.id', 'desc')->get(),
                        'result_plans' => DB::table('stock_result_pin')->leftJoin("user", function ($join) {
                            $join->on("user.username", "=", "stock_result_pin.added_by");
                        })->orderBy('stock_result_pin.id', 'desc')->get(),
                    ]);
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }

    public function ApiBalance(Request $request)
    {
        if ($this->validateOrigin($request)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    $adex_api = DB::table('adex_api')->first();
                    $api_website = DB::table('web_api')->first();
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $api_website->adex_website1 . "/api/user/");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt(
                        $ch,
                        CURLOPT_HTTPHEADER,
                        [
                            "Authorization: Basic " . base64_encode($adex_api->adex1_username . ":" . $adex_api->adex1_password),
                        ]
                    );
                    $json = curl_exec($ch);
                    curl_close($ch);
                    $decode_adex = json_decode($json, true);
                    if (isset($decode_adex)) {
                        if (isset($decode_adex['status'])) {
                            if ($decode_adex['status'] == 'success') {
                                $admin_balance = '₦' . $decode_adex['balance'];
                            } else {
                                $admin_balance = 'API NOT CONNECTED';
                            }
                        } else {
                            $admin_balance = 'API NOT CONNECTED';
                        }
                    } else {
                        $admin_balance = 'API NOT CONNECTED';
                    }
                    return response()->json([
                        'status' => 'success',
                        'admin_url' => $api_website->adex_website1,
                        'balance' => $admin_balance
                    ]);
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User ID required'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }

    protected function validateOrigin(Request $request)
    {
        $allowedOrigins = array_filter(array_map('trim', explode(',', config('adex.app_key', ''))));
        $origin = $request->headers->get('origin');
        $referer = $request->headers->get('referer');
        $host = $request->getHost();
        $fullUrl = $request->getSchemeAndHttpHost();

        $originNormalized = rtrim($origin ?: '', '/');
        $isSameOrigin = false;
        $refererMatches = false;

        if (!$origin && $referer) {
            $refererHost = parse_url($referer, PHP_URL_HOST);
            if ($refererHost === $host) {
                $isSameOrigin = true;
            }
        }

        if ($referer) {
            $refererUrl = parse_url($referer, PHP_URL_SCHEME) . '://' . parse_url($referer, PHP_URL_HOST);
            $refererNormalized = rtrim($refererUrl ?: '', '/');
            $refererMatches = in_array($refererNormalized, $allowedOrigins);
        }

        return in_array($originNormalized, $allowedOrigins)
            || $refererMatches
            || $isSameOrigin
            || config('adex.device_key') === $request->header('Authorization')
            || in_array($fullUrl, $allowedOrigins);
    }
}
