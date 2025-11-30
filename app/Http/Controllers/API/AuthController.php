<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MailController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $allowedOrigins = array_filter(array_map('trim', explode(',', config('adex.app_key', ''))));
        $origin = $request->headers->get('origin');
        $originNormalized = rtrim($origin ?: '', '/');
        
        if (in_array($originNormalized, $allowedOrigins) || config('adex.device_key') === $request->header('Authorization')) {
            $validator = validator::make($request->all(), [
                'firstName' => 'required|string|max:100',
                'lastName' => 'required|string|max:100',
                'username' => 'required|unique:user,username|max:12|string|alpha_num',
                'gender' => 'required|in:male,female,other',
                'state' => 'required|string|max:100',
                'city' => 'required|string|max:100',
                'streetAddress' => 'required|string|max:255',
                'phone' => 'required|numeric|unique:user,phone|digits:11',
                'email' => 'required|unique:user,email|max:255|email',
                'password' => 'required|min:8',
                'pin' => 'required|numeric|digits:4'
            ], [
                'firstName.required' => 'First Name is Required',
                'lastName.required' => 'Last Name is Required',
                'username.required' => 'Username is Required',
                'username.unique' => 'Username already Taken',
                'username.max' => 'Username Maximum Length is 12',
                'gender.required' => 'Gender is Required',
                'gender.in' => 'Gender must be male, female, or other',
                'state.required' => 'State is Required',
                'city.required' => 'City is Required',
                'streetAddress.required' => 'Street Address is Required',
                'phone.required' => 'Phone Number is Required',
                'phone.unique' => 'Phone Number already Taken',
                'phone.numeric' => 'Phone Number Must be Numeric',
                'phone.digits' => 'Phone Number Must be 11 Digits',
                'email.required' => 'E-mail is Required',
                'email.unique' => 'Email Already Taken',
                'email.email' => 'Invalid Email Format',
                'password.required' => 'Password is Required',
                'password.min' => 'Password Must be at Least 8 Characters',
                'pin.required' => 'Transaction PIN is Required',
                'pin.numeric' => 'Transaction PIN Must be Numeric',
                'pin.digits' => 'Transaction PIN Must be 4 Digits'
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
                } else {
                    $user = new User();
                    // Combine first name and last name for name field
                    $user->name = trim($request->firstName . ' ' . $request->lastName);
                    $user->username = $request->username;
                    $user->email = $request->email;
                    $user->phone = $request->phone;
                    $user->password = password_hash($request->password, PASSWORD_DEFAULT, array('cost' => 16));
                    // $user->password = Hash::make($request->password);
                    $user->apikey = bin2hex(openssl_random_pseudo_bytes(30));
                    $user->app_key = $user->apikey;
                    $user->bal = '0.00';
                    $user->refbal = '0.00';
                    $user->ref = $request->ref ?? null;
                    $user->type = 'SMART';
                    $user->date = Carbon::now("Africa/Lagos")->toDateTimeString();
                    $user->kyc = '0';
                    $user->status = '0';
                    $user->user_limit = $this->adex_key()->default_limit;
                    $user->pin = $request->pin;
                    // Save new address fields
                    $user->address = trim($request->streetAddress . ', ' . $request->city . ', ' . $request->state);
                    // Save gender (will be added via migration)
                    if (isset($request->gender)) {
                        $user->gender = $request->gender;
                    }
                    $user->save();
                    if ($user != null) {
                        $this->monnify_account($user->username);
                        $this->paystack_account($user->username);
                        $this->insert_stock($user->username);
                        $user = DB::table('user')->where(['id' => $user->id])->first();
                        $user_details = [
                            'username' => $user->username,
                            'name' => $user->name,
                            'phone' => $user->phone,
                            'email' => $user->email,
                            'bal' => (float) $user->bal, // Send raw number for frontend formatting
                            'refbal' => (float) $user->refbal, // Send raw number for frontend formatting
                            'kyc' => $user->kyc,
                            'type' => $user->type,
                            'pin' => $user->pin,
                            'profile_image' => $user->profile_image,
                            'sterlen' => $user->sterlen,
                            'vdf' => $user->vdf,
                            'fed' => $user->fed,
                            'wema' => $user->wema,
                            'rolex' => $user->rolex,
                            'address' => $user->address,
                            'webhook' => $user->webhook,
                            'about' => $user->about,
                            'apikey' => $user->apikey,
                            'is_bvn' => $user->bvn == null ? false : true
                        ];

                        $token = $this->generatetoken($user->id);
                        $use = $this->core();
                        $general = $this->general();
                        if ($use != null) {
                            if ($use->is_verify_email) {
                                $otp = random_int(100000, 999999);
                                
                                // Set OTP expiration to 60 seconds from now
                                $otpExpiresAt = Carbon::now()->addSeconds(60);
                                
                                $data = [
                                    'otp' => $otp,
                                    'otp_expires_at' => $otpExpiresAt
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
                                    'pin' => $user->pin,
                                    'sender_mail' => $general->app_email,
                                    'app_name' => env('APP_NAME'),
                                    'otp' => $otp
                                ];
                                MailController::send_mail($email_data, 'email.verify');
                                return response()->json([
                                    'status' => 'verify',
                                    'username' => $user->username,
                                    'token' => $token,
                                    'user' => $user_details
                                ]);
                            } else {
                                $data = [
                                    'status' => 1
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
                                    'app_name' => $general->app_name,
                                    'pin' => $user->pin,
                                ];
                                MailController::send_mail($email_data, 'email.welcome');
                                return response()->json([
                                    'status' => 'success',
                                    'username' => $user->username,
                                    'token' => $token,
                                    'user' => $user_details
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
                                'app_name' => $general->app_name,
                                'pin' => $user->pin,
                            ];
                            MailController::send_mail($email_data, 'email.welcome');
                            return response()->json([
                                'status' => 'success',
                                'username' => $user->username,
                                'token' => $token,
                                'user' => $user_details
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
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function account(Request $request)
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
            try {
                $user_token = $request->id;
                $real_token = $this->verifytoken($user_token);
                if (!is_null($real_token)) {
                    $adex_check = DB::table('user')->where('id', $real_token);
                    if ($adex_check->count() == 1) {
                        $user = $adex_check->get()[0];
                        try {
                            $this->monnify_account($user->username);
                        } catch (\Exception $e) {
                            // Log but don't fail if monnify_account fails
                            \Log::warning('monnify_account failed: ' . $e->getMessage());
                        }
                        try {
                            $this->insert_stock($user->username);
                        } catch (\Exception $e) {
                            // Log but don't fail if insert_stock fails
                            \Log::warning('insert_stock failed: ' . $e->getMessage());
                        }
                        
                        // Check and create BellBank virtual account if missing
                        try {
                            $bellAccount = \App\Models\BellAccount::where('user_id', $user->id)->first();
                            if (!$bellAccount) {
                                $userModel = \App\Models\User::find($user->id);
                                if ($userModel) {
                                    \App\Jobs\CreateBellBankVirtualAccount::dispatch($userModel);
                                    \Log::info('BellBank virtual account creation job dispatched for existing user', [
                                        'user_id' => $user->id,
                                        'username' => $user->username,
                                    ]);
                                }
                            }
                        } catch (\Exception $e) {
                            \Log::warning('Failed to check/create BellBank virtual account: ' . $e->getMessage());
                        }
                        
                        $user = DB::table('user')->where(['id' => $user->id])->first();
                        
                        // CRITICAL: Always check status == 0 FIRST before any other checks
                        // This ensures unverified users (status 0) always require verification,
                        // even if OTP has expired or been cleared
                        if ($user->status == 0) {
                            // For unverified users, check if OTP expired and resend if needed
                            $hasOtp = !empty($user->otp);
                            $otpExpired = false;
                            
                            if ($hasOtp && isset($user->otp_expires_at) && $user->otp_expires_at) {
                                try {
                                    if (Carbon::now()->gt(Carbon::parse($user->otp_expires_at))) {
                                        $otpExpired = true;
                                        // Clear expired OTP for unverified user
                                        $this->updateData([
                                            'otp' => null,
                                            'otp_expires_at' => null
                                        ], 'user', ['id' => $user->id]);
                                        // Refresh user data after clearing OTP
                                        $user = DB::table('user')->where(['id' => $user->id])->first();
                                    }
                                } catch (\Exception $e) {
                                    \Log::warning('OTP expiration check failed for unverified user: ' . $e->getMessage());
                                }
                            }
                            
                            $user_details = $this->getUserDetailsWithBellBank($user);
                            
                            // Always return 'verify' for status 0 users, regardless of OTP state
                            return response()->json([
                                'status' => 'verify',
                                'message' => 'Account Not Yet Verified' . ($otpExpired ? '. Please request a new OTP.' : ''),
                                'user' => $user_details
                            ]);
                        } else if ($user->status == 1) {
                            // Get user details with BellBank account info
                            $user_details = $this->getUserDetailsWithBellBank($user);
                            
                            // Check if user has pending OTP that needs verification
                            $hasPendingOtp = !empty($user->otp);
                            $otpExpired = false;
                            
                            // Check if OTP is expired (if column exists)
                            if ($hasPendingOtp && isset($user->otp_expires_at) && $user->otp_expires_at) {
                                try {
                                    if (Carbon::now()->gt(Carbon::parse($user->otp_expires_at))) {
                                        $otpExpired = true;
                                        // Clear expired OTP
                                        $this->updateData([
                                            'otp' => null,
                                            'otp_expires_at' => null
                                        ], 'user', ['id' => $user->id]);
                                    }
                                } catch (\Exception $e) {
                                    \Log::warning('OTP expiration check failed: ' . $e->getMessage());
                                }
                            }
                            
                            // If user has pending OTP that hasn't expired, require verification
                            if ($hasPendingOtp && !$otpExpired) {
                                return response()->json([
                                    'status' => 'verify',
                                    'message' => 'OTP verification required',
                                    'user' => $user_details
                                ]);
                            }
                            
                            // User is verified and has no pending OTP
                            return response()->json([
                                'status' => 'success',
                                'message' => 'account verified',
                                'user' => $user_details
                            ]);
                        } else if ($user->status == '2') {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Account Banned'
                            ])->setStatusCode(403);
                        } elseif ($user->status == '3') {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Account Deactivated'
                            ])->setStatusCode(403);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Unable to Get User'
                            ])->setStatusCode(403);
                        }
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Not Allowed',
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'AccessToken Expired'
                    ])->setStatusCode(403);
                }
            } catch (\Exception $e) {
                \Log::error('Account endpoint error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'user_token' => substr($request->id ?? '', 0, 10) . '...'
                ]);
                
                // Try to get user data even if BellBank lookup fails
                try {
                    $user_token = $request->id;
                    $real_token = $this->verifytoken($user_token);
                    if (!is_null($real_token)) {
                        $user = DB::table('user')->where('id', $real_token)->first();
                        if ($user) {
                            // Return basic user data without BellBank if that's what's failing
                            $user_details = [
                                'username' => $user->username ?? '',
                                'name' => $user->name ?? '',
                                'phone' => $user->phone ?? '',
                                'email' => $user->email ?? '',
                                'bal' => (float) ($user->bal ?? 0),
                                'refbal' => (float) ($user->refbal ?? 0),
                                'kyc' => $user->kyc ?? 0,
                                'type' => $user->type ?? 'SMART',
                                'pin' => $user->pin ?? '',
                                'profile_image' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
                                'apikey' => $user->apikey ?? null,
                                'is_bvn' => isset($user->bvn) && $user->bvn != null,
                                'status' => isset($user->status) ? (int) $user->status : 0,
                                'otp' => $user->otp ?? null,
                                'bellbank_account' => null // Set to null if lookup fails
                            ];
                            
                            if ($user->status == 0) {
                                return response()->json([
                                    'status' => 'verify',
                                    'message' => 'Account Not Yet Verified',
                                    'user' => $user_details
                                ]);
                            } else if ($user->status == 1) {
                                return response()->json([
                                    'status' => 'success',
                                    'message' => 'account verified',
                                    'user' => $user_details
                                ]);
                            }
                        }
                    }
                } catch (\Exception $fallbackError) {
                    \Log::error('Account endpoint fallback also failed: ' . $fallbackError->getMessage());
                }
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'An error occurred. Please try again later.'
                ])->setStatusCode(500);
            }
        } else {
            // Origin validation failed - return error status but don't use 403 to avoid logout
            // This allows frontend to retry or maintain session
            \Log::warning('Account endpoint origin validation failed', [
                'origin' => $origin,
                'referer' => $referer,
                'fullUrl' => $fullUrl,
                'allowedOrigins' => $allowedOrigins
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Origin validation failed. Please refresh the page.'
            ]);
        }
    }
    public function verify(Request $request)
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
            $adex_check = DB::table('user')->where('email', $request->email);
            if ($adex_check->count() == 1) {
                $user = $adex_check->get()[0];
                $this->monnify_account($user->username);
                $this->insert_stock($user->username);
                $user = DB::table('user')->where(['id' => $user->id])->first();
                $user_details = $this->getUserDetailsWithBellBank($user);
                // Check if OTP is expired (if column exists)
                if (isset($user->otp_expires_at) && $user->otp_expires_at) {
                    try {
                        if (Carbon::now()->gt(Carbon::parse($user->otp_expires_at))) {
                            return response()->json([
                                'status' => 403,
                                'message' => 'OTP has expired. Please request a new one.'
                            ])->setStatusCode(403);
                        }
                    } catch (\Exception $e) {
                        // If parsing fails, continue with verification
                        \Log::warning('OTP expiration check failed: ' . $e->getMessage());
                    }
                }
                
                if ($user->otp == $request->code) {
                    // Check if this is a new user verification (status = 0) or login verification (status = 1)
                    $isNewUser = $user->status == 0;
                    
                    //if success
                    $data = [
                        'status' => '1',
                        'otp' => null,
                        'otp_expires_at' => null
                    ];
                    $tableid = [
                        'id' => $user->id
                    ];
                    $general = $this->general();
                    $this->updateData($data, 'user', $tableid);
                    
                    // Refresh user data after status update
                    $user = DB::table('user')->where(['id' => $user->id])->first();
                    
                    // STEP 4: Only after OTP success → mark device as trusted
                    // Store device as trusted after OTP verification
                    try {
                        $this->storeDeviceInfo($user->id, $request, true);
                        \Log::info('Device marked as trusted after OTP verification', [
                            'user_id' => $user->id,
                            'device_fingerprint' => substr($this->generateDeviceFingerprint($request), 0, 20) . '...'
                        ]);
                    } catch (\Exception $e) {
                        \Log::warning('Failed to store device info: ' . $e->getMessage());
                    }
                    
                    // Only send welcome email for new users (first time verification), not for login OTP verification
                    if ($isNewUser) {
                        $email_data = [
                            'name' => $user->name,
                            'email' => $user->email,
                            'username' => $user->username,
                            'title' => 'WELCOME EMAIL',
                            'sender_mail' => $general->app_email,
                            'system_email' => $general->app_email,
                            'app_name' => $general->app_name,
                            'pin' => $user->pin,
                        ];
                        try {
                            MailController::send_mail($email_data, 'email.welcome');
                        } catch (\Exception $e) {
                            \Log::warning('Failed to send welcome email: ' . $e->getMessage());
                        }
                        
                        // Create BellBank virtual account for new user (after successful verification)
                        try {
                            $userModel = \App\Models\User::find($user->id);
                            if ($userModel) {
                                \App\Jobs\CreateBellBankVirtualAccount::dispatch($userModel);
                                \Log::info('BellBank virtual account creation job dispatched', [
                                    'user_id' => $user->id,
                                    'username' => $user->username,
                                ]);
                            }
                        } catch (\Exception $e) {
                            \Log::warning('Failed to dispatch BellBank virtual account creation: ' . $e->getMessage());
                        }
                    }
                    
                    // Generate token for authenticated user
                    try {
                        $token = $this->generatetoken($user->id);
                    } catch (\Exception $e) {
                        \Log::error('Failed to generate token after OTP verification: ' . $e->getMessage());
                        $token = $user->apikey ?? null;
                    }
                    
                    // Get updated user details with error handling
                    try {
                        $user_details = $this->getUserDetailsWithBellBank($user);
                    } catch (\Exception $e) {
                        \Log::error('Failed to get user details with BellBank: ' . $e->getMessage(), [
                            'user_id' => $user->id,
                            'trace' => $e->getTraceAsString()
                        ]);
                        // Fallback to basic user details if BellBank lookup fails
                        $user_details = [
                            'username' => $user->username,
                            'name' => $user->name,
                            'phone' => $user->phone,
                            'email' => $user->email,
                            'bal' => (float) $user->bal,
                            'refbal' => (float) $user->refbal,
                            'kyc' => $user->kyc,
                            'type' => $user->type,
                            'pin' => $user->pin,
                            'apikey' => $user->apikey,
                            'is_bvn' => $user->bvn == null ? false : true,
                            'status' => isset($user->status) ? (int) $user->status : 0, // Critical: Include status for frontend verification checks
                            'otp' => $user->otp ?? null, // Include OTP so frontend can check for pending verification
                        ];
                    }
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => 'account verified',
                        'token' => $token,
                        'user' => $user_details
                    ]);
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Invalid OTP'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 403,
                    'message' => 'Unable to verify user'
                ])->setStatusCode(403);
            }
        } else {
            // Return JSON error instead of redirect for API requests
            return response()->json([
                'status' => 403,
                'message' => 'Unable to verify OTP. Please check your connection and try again, or request a new OTP.',
            ])->setStatusCode(403);
        }
    }
    public function login(Request $request)
    {
        $allowedOrigins = array_filter(array_map('trim', explode(',', config('adex.app_key', ''))));
        $origin = $request->headers->get('origin');
        $originNormalized = rtrim($origin ?: '', '/');
        
        if (in_array($originNormalized, $allowedOrigins) || config('adex.device_key') === $request->header('Authorization')) {
            //our login function over here
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required'
            ], [
                'username.required' => 'Your Phone Number or Email is Required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 403,
                    'message' => $validator->errors()->first()
                ])->setStatusCode(403);
            } else {
                // Allow login with phone or email only (username removed)
                $check_system = User::where(function($query) use ($request) {
                    $query->where('phone', $request->username)
                          ->orWhere('email', $request->username);
                });
                if ($check_system->count() == 1) {
                    $user = $check_system->get()[0];
                    
                    // STEP 1: Always verify password first - DO NOT mark device trusted yet
                    $hash = substr(sha1(md5($request->password)), 3, 10);
                    $mdpass = md5($request->password);
                    $passwordValid = (password_verify($request->password, $user->password)) 
                        || ($request->password == $user->password) 
                        || ($hash == $user->password) 
                        || ($mdpass == $user->password);
                    
                    if (!$passwordValid) {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Invalid Phone Number, Email or Password'
                        ])->setStatusCode(403);
                    }
                    
                    // Check user status
                    if ($user->status == 2) {
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
                        // Account not verified
                        $user_details = [
                            'username' => $user->username,
                            'name' => $user->name,
                            'phone' => $user->phone,
                            'email' => $user->email,
                            'bal' => (float) $user->bal,
                            'refbal' => (float) $user->refbal,
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
                            'vdf' => $user->vdf,
                            'apikey' => $user->apikey,
                            'is_bvn' => $user->bvn == null ? false : true
                        ];
                        return response()->json([
                            'status' => 'verify',
                            'message' => $user->username . ' Your Account Not Yet verified',
                            'user' => $user_details,
                            'token' => $this->generatetoken($user->id),
                        ]);
                    }
                    
                    // Password is valid, user status is 1 (active)
                    
                    // ADMIN BYPASS: Skip OTP for admin users
                    if ($user->type === 'ADMIN') {
                        // Mark device as trusted for admin
                        try {
                            $deviceFingerprint = $this->generateDeviceFingerprint($request);
                            $this->storeDeviceInfo($user->id, $request, true);
                            \Log::info('Admin user logged in without OTP', [
                                'user_id' => $user->id,
                                'username' => $user->username,
                                'device_fingerprint' => substr($deviceFingerprint, 0, 20) . '...'
                            ]);
                        } catch (\Exception $e) {
                            \Log::warning('Failed to store device info for admin: ' . $e->getMessage());
                        }
                        
                        // Initialize monnify and stock for admin
                        try {
                            $this->monnify_account($user->username);
                            $this->insert_stock($user->username);
                        } catch (\Exception $e) {
                            \Log::warning('Failed to initialize monnify/stock for admin: ' . $e->getMessage());
                        }
                        
                        // Get updated user details
                        $user = DB::table('user')->where(['id' => $user->id])->first();
                        $user_details = $this->getUserDetailsWithBellBank($user);
                        
                        $token = $this->generatetoken($user->id);
                        
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Login successful',
                            'user' => $user_details,
                            'token' => $token
                        ]);
                    }
                    
                    // STEP 2: Check session status (for non-admin users)
                    $deviceFingerprint = $this->generateDeviceFingerprint($request);
                    $device = DB::table('user_devices')
                        ->where('user_id', $user->id)
                        ->where('device_fingerprint', $deviceFingerprint)
                        ->where('is_trusted', 1)
                        ->first();
                    
                    $isTrustedDevice = $device !== null;
                    
                    // Per user requirement: "OTP must NEVER be skipped after password login"
                    // So we ALWAYS require OTP after password verification (except for admin)
                    // Auto-login with token should be handled in account/my-account endpoint
                    $requiresOtp = true; // ALWAYS require OTP after password login (for non-admin users)
                    
                    // Check inactivity (for logging purposes only)
                    $hoursSinceActivity = null;
                    if ($isTrustedDevice && $device && $device->last_login_at) {
                        $lastActivity = Carbon::parse($device->last_login_at);
                        $hoursSinceActivity = Carbon::now()->diffInHours($lastActivity);
                    }
                    
                    // Log for debugging
                    \Log::info('Login attempt after password verification', [
                        'user_id' => $user->id,
                        'username' => $user->username,
                        'device_fingerprint' => substr($deviceFingerprint, 0, 20) . '...',
                        'is_trusted_device' => $isTrustedDevice ? 'YES' : 'NO',
                        'requires_otp' => 'YES (always after password login)',
                        'hours_since_activity' => $hoursSinceActivity ?? 'N/A',
                        'ip' => $request->ip(),
                        'user_agent' => substr($request->header('User-Agent', ''), 0, 50),
                        'last_activity' => $device->last_login_at ?? 'N/A'
                    ]);
                    
                    // STEP 3: ALWAYS send OTP after password login (never skip)
                    // OTP must NEVER be skipped after password login
                    if ($requiresOtp) {
                        // Generate new OTP for new device only
                        $otp = random_int(100000, 999999);
                        
                        // Get device details
                        $userAgent = $request->header('User-Agent', '');
                        $ipAddress = $request->ip();
                        $browser = 'Unknown';
                        $os = 'Unknown';
                        
                        // Simple browser detection
                        if (strpos($userAgent, 'Chrome') !== false) {
                            $browser = 'Chrome';
                        } elseif (strpos($userAgent, 'Firefox') !== false) {
                            $browser = 'Firefox';
                        } elseif (strpos($userAgent, 'Safari') !== false) {
                            $browser = 'Safari';
                        } elseif (strpos($userAgent, 'Edge') !== false) {
                            $browser = 'Edge';
                        }
                        
                        // Simple OS detection
                        if (strpos($userAgent, 'Windows') !== false) {
                            $os = 'Windows';
                        } elseif (strpos($userAgent, 'Mac') !== false) {
                            $os = 'macOS';
                        } elseif (strpos($userAgent, 'Linux') !== false) {
                            $os = 'Linux';
                        } elseif (strpos($userAgent, 'Android') !== false) {
                            $os = 'Android';
                        } elseif (strpos($userAgent, 'iOS') !== false || strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
                            $os = 'iOS';
                        }
                        
                        // Save OTP with expiration (60 seconds)
                        $otpExpiresAt = Carbon::now()->addSeconds(60);
                        $this->updateData([
                            'otp' => $otp,
                            'otp_expires_at' => $otpExpiresAt
                        ], 'user', ['id' => $user->id]);
                        
                        $message = 'OTP sent to your email for verification.';
                        
                        // Send OTP email only for new device (not for logout OTP)
                        $general = $this->general();
                        $email_data = [
                            'name' => $user->name,
                            'email' => $user->email,
                            'username' => $user->username,
                            'title' => 'OTP for Login Attempt',
                            'sender_mail' => $general->app_email ?? 'noreply@kobopoint.com',
                            'app_name' => $general->app_name ?? env('APP_NAME', 'KoboPoint'),
                            'otp' => $otp,
                            'app_url' => env('APP_URL'),
                            'browser' => $browser,
                            'os' => $os,
                            'ip_address' => $ipAddress,
                            'device_info' => $browser . ' on ' . $os
                        ];
                        
                        try {
                            MailController::send_mail($email_data, 'email.otp_verification');
                        } catch (\Exception $e) {
                            \Log::error('Failed to send OTP email: ' . $e->getMessage());
                        }
                        
                        // Return user details and token for OTP verification
                        $user = DB::table('user')->where(['id' => $user->id])->first();
                        $user_details = [
                            'username' => $user->username,
                            'name' => $user->name,
                            'phone' => $user->phone,
                            'email' => $user->email,
                            'bal' => (float) $user->bal,
                            'refbal' => (float) $user->refbal,
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
                            'vdf' => $user->vdf,
                            'apikey' => $user->apikey,
                            'is_bvn' => $user->bvn == null ? false : true
                        ];
                        
                        $token = $this->generatetoken($user->id);
                        return response()->json([
                            'status' => 'verify',
                            'message' => $message,
                            'user' => $user_details,
                            'token' => $token
                        ]);
                    }
                    
                    // This code should never be reached since we always require OTP after password login
                    // But just in case, return verify status
                    $this->monnify_account($user->username);
                    $this->insert_stock($user->username);
                    $user = DB::table('user')->where(['id' => $user->id])->first();
                    $user_details = [
                        'username' => $user->username,
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'bal' => (float) $user->bal,
                        'refbal' => (float) $user->refbal,
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
                        'vdf' => $user->vdf,
                        'apikey' => $user->apikey,
                        'is_bvn' => $user->bvn == null ? false : true
                    ];
                    
                    // Always require OTP after password login - return verify status
                    return response()->json([
                        'status' => 'verify',
                        'message' => 'OTP verification required. Please check your email.',
                        'user' => $user_details,
                        'token' => $this->generatetoken($user->id)
                    ]);
                } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Invalid Phone Number, Email or Password'
                        ])->setStatusCode(403);
                }
            }
        } else {
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function resendOtp(Request $request)
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
            if (isset($request->id)) {
                $sel_user = DB::table('user')->where('email', $request->id);
                if ($sel_user->count() == 1) {
                    $user = $sel_user->get()[0];
                    $general = $this->general();
                    $otp = random_int(100000, 999999);
                    
                    // Set OTP expiration to 60 seconds from now
                    $otpExpiresAt = Carbon::now()->addSeconds(60);
                    
                    $data = [
                        'otp' => $otp,
                        'otp_expires_at' => $otpExpiresAt
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
                    return response()->json([
                        'status' => 'success',
                        'message' => 'New OTP Resent to Your Email',
                        'expires_at' => $otpExpiresAt->toISOString()
                    ]);
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Unable to Detect User'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 403,
                    'message' => 'An Error Occured'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System',
            ])->setStatusCode(403);
        }
    }

    /**
     * Logout user - destroy session and device trust, force OTP next login
     */
    public function logout(Request $request)
    {
        $allowedOrigins = array_filter(array_map('trim', explode(',', config('adex.app_key', ''))));
        $origin = $request->headers->get('origin');
        $originNormalized = rtrim($origin ?: '', '/');
        
        if (in_array($originNormalized, $allowedOrigins) || config('adex.device_key') === $request->header('Authorization')) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string'
            ], [
                'id.required' => 'User token is required',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 403,
                    'message' => $validator->errors()->first()
                ])->setStatusCode(403);
            }
            
            $user_token = $request->id;
            $real_token = $this->verifytoken($user_token);
            
            if (!is_null($real_token)) {
                // STEP 4: On logout - destroy session and device trust
                try {
                    // Clear OTP
                    $this->updateData([
                        'otp' => null,
                        'otp_expires_at' => null
                    ], 'user', ['id' => $real_token]);
                    
                    // Get device fingerprint
                    $deviceFingerprint = $this->generateDeviceFingerprint($request);
                    
                    // Delete trusted-device record (force OTP next login)
                    DB::table('user_devices')
                        ->where('user_id', $real_token)
                        ->where('device_fingerprint', $deviceFingerprint)
                        ->update([
                            'is_trusted' => 0,
                            'last_login_at' => null
                        ]);
                    
                    \Log::info('User logged out', [
                        'user_id' => $real_token,
                        'device_fingerprint' => substr($deviceFingerprint, 0, 20) . '...',
                        'device_trust_cleared' => true
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to clear session on logout: ' . $e->getMessage());
                }
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Logged out successfully. OTP will be required on next login.'
                ]);
            } else {
                return response()->json([
                    'status' => 403,
                    'message' => 'Invalid token'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }

    /**
     * Generate device fingerprint from request
     */
    private function generateDeviceFingerprint(Request $request)
    {
        $userAgent = $request->header('User-Agent', '');
        $ip = $request->ip();
        $acceptLanguage = $request->header('Accept-Language', '');
        $acceptEncoding = $request->header('Accept-Encoding', '');
        
        // Create a unique fingerprint
        $fingerprint = hash('sha256', $userAgent . $ip . $acceptLanguage . $acceptEncoding);
        
        return $fingerprint;
    }

    /**
     * Check if device is trusted
     */
    private function isDeviceTrusted($userId, $deviceFingerprint)
    {
        $device = DB::table('user_devices')
            ->where('user_id', $userId)
            ->where('device_fingerprint', $deviceFingerprint)
            ->where('is_trusted', 1)
            ->first();
            
        return $device !== null;
    }

    /**
     * Store or update device information
     */
    private function storeDeviceInfo($userId, Request $request, $isTrusted = false)
    {
        $deviceFingerprint = $this->generateDeviceFingerprint($request);
        
        // Parse user agent
        $userAgent = $request->header('User-Agent', '');
        $browser = 'Unknown';
        $os = 'Unknown';
        $deviceType = 'desktop';
        
        // Simple browser detection
        if (strpos($userAgent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            $browser = 'Edge';
        }
        
        // Simple OS detection
        if (strpos($userAgent, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (strpos($userAgent, 'Mac') !== false) {
            $os = 'macOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $os = 'Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $os = 'Android';
            $deviceType = 'mobile';
        } elseif (strpos($userAgent, 'iOS') !== false || strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
            $os = 'iOS';
            $deviceType = strpos($userAgent, 'iPad') !== false ? 'tablet' : 'mobile';
        }
        
        // Check if device exists
        $existingDevice = DB::table('user_devices')
            ->where('user_id', $userId)
            ->where('device_fingerprint', $deviceFingerprint)
            ->first();
            
        if ($existingDevice) {
            // Update last login and activity
            DB::table('user_devices')
                ->where('id', $existingDevice->id)
                ->update([
                    'last_login_at' => Carbon::now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $userAgent,
                    'is_trusted' => $isTrusted ? 1 : $existingDevice->is_trusted, // Only update if explicitly setting to trusted
                    'updated_at' => Carbon::now()
                ]);
        } else {
            // Create new device record
            DB::table('user_devices')->insert([
                'user_id' => $userId,
                'device_fingerprint' => $deviceFingerprint,
                'browser' => $browser,
                'os' => $os,
                'device_type' => $deviceType,
                'ip_address' => $request->ip(),
                'user_agent' => $userAgent,
                'is_trusted' => $isTrusted ? 1 : 0,
                'last_login_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
        
        return $deviceFingerprint;
    }

    /**
     * Get user details with BellBank account info
     */
    protected function getUserDetailsWithBellBank($user)
    {
        $bellAccount = null;
        
        // Get BellBank virtual account with error handling
        try {
            if (class_exists(\App\Models\BellAccount::class)) {
                $bellAccount = \App\Models\BellAccount::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->first();
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch BellBank account: ' . $e->getMessage(), [
                'user_id' => $user->id ?? 'unknown'
            ]);
        }
        
        $user_details = [
            'username' => $user->username ?? '',
            'name' => $user->name ?? '',
            'phone' => $user->phone ?? '',
            'email' => $user->email ?? '',
            'bal' => isset($user->bal) ? (float) $user->bal : 0.00,
            'refbal' => isset($user->refbal) ? (float) $user->refbal : 0.00,
            'kyc' => $user->kyc ?? '0',
            'type' => $user->type ?? 'SMART',
            'pin' => $user->pin ?? '',
            'profile_image' => $user->profile_image ?? null,
            'sterlen' => $user->sterlen ?? null,
            'fed' => $user->fed ?? null,
            'wema' => $user->wema ?? null,
            'rolex' => $user->rolex ?? null,
            'vdf' => $user->vdf ?? null,
            'address' => $user->address ?? null,
            'webhook' => $user->webhook ?? null,
            'about' => $user->about ?? null,
            'apikey' => $user->apikey ?? null,
            'is_bvn' => isset($user->bvn) && $user->bvn != null,
            'status' => isset($user->status) ? (int) $user->status : 0, // Critical: Include status for frontend verification checks
            'otp' => $user->otp ?? null, // Include OTP so frontend can check for pending verification
            'bellbank_account' => $bellAccount ? [
                'account_number' => $bellAccount->account_number ?? null,
                'bank_name' => $bellAccount->bank_name ?: 'BellBank',
                'account_name' => isset($bellAccount->metadata['accountName']) ? $bellAccount->metadata['accountName'] : ($user->username ?? ''),
                'status' => $bellAccount->status ?? 'pending',
            ] : null
        ];
        
        return $user_details;
    }

    /**
     * Update device activity timestamp
     */
    private function updateDeviceActivity($userId, $deviceFingerprint, Request $request)
    {
        $existingDevice = DB::table('user_devices')
            ->where('user_id', $userId)
            ->where('device_fingerprint', $deviceFingerprint)
            ->first();
            
        if ($existingDevice) {
            DB::table('user_devices')
                ->where('id', $existingDevice->id)
                ->update([
                    'last_login_at' => Carbon::now(),
                    'ip_address' => $request->ip(),
                    'updated_at' => Carbon::now()
                ]);
        }
    }
}
