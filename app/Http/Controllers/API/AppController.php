<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AppController extends Controller
{
    public function system(Request $request)
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
                $core = $this->core();
                $feature = $this->feature();
                $general = $this->general();
                $bank = DB::table('adex_key')->select('account_number', 'account_name', 'bank_name', 'min', 'max')->first();
                
                return response()->json([
                    'status' => 'success',
                    'setting' => $core,
                    'feature' => $feature,
                    'general' => $general,
                    'bank' => $bank
                ]);
            } catch (\Exception $e) {
                \Log::error('System endpoint error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'origin' => $origin,
                    'allowedOrigins' => $allowedOrigins
                ]);
                return response()->json([
                    'status' => false,
                    'message' => 'An error occurred. Please try again later.'
                ])->setStatusCode(500);
            }
        } else {
            \Log::warning('System endpoint origin mismatch', [
                'origin' => $origin,
                'originNormalized' => $originNormalized,
                'referer' => $referer,
                'fullUrl' => $fullUrl,
                'allowedOrigins' => $allowedOrigins
            ]);
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }

    public function apiUpgrade(Request $request)
    {
        $explode_url = explode(',', config('adex.app_key'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            $validator = validator::make($request->all(), [
                'username' => 'required|max:25',
                'url' => 'required|url',
            ], [
                'url.url' => 'Invalid URL it must contain (https or www)',
                'url.required' => 'Your website URL is Needed For Verification'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 403
                ])->setStatusCode(403);
            } else {
                //api user dat need upgrade
                $check_me = [
                    'username' => $request->username,
                    'status' => 1
                ];
                $check_user = DB::table('user')->where($check_me);
                if ($check_user->count() == 1) {
                    $user = $check_user->get()[0];
                    $general = $this->general();
                    $date = $this->system_date();
                    $ref = $this->generate_ref('API_UPGRADE');
                    $get_admins = DB::table('user')->where('status', '1')->where(function ($query) {
                        $query->where('type', 'ADMIN')
                            ->orWhere('type', 'CUSTOMER');
                    });
                    if ($get_admins->count() > 0) {
                        foreach ($get_admins->get() as $send_admin) {
                            $email_data = [
                                'name' => $user->name,
                                'email' => $send_admin->email,
                                'username' => $user->username,
                                'title' => 'API PACKAGE REQUEST',
                                'sender_mail' => $general->app_email,
                                'user_email' => $user->email,
                                'app_name' => $general->app_name,
                                'website' => $request->url,
                                'date' => $date,
                                'transid' => $ref,
                                'app_phone' => $general->app_phone
                            ];
                            MailController::send_mail($email_data, 'email.apirequest');
                        }
                        $insert_data = [
                            'username' => $user->username,
                            'date' => $date,
                            'transid' => $ref,
                            'status' => 0,
                            'title' => 'API UPGRAGE',
                            'message' => $user->username . ', want is account to be upgraded to API Package and is website url is ' . $request->url
                        ];
                        $insert = $this->inserting_data('request', $insert_data);
                        if ($insert) {
                            return response()->json([
                                'status' => 'success',
                                'message' => 'Your Request has been received and it will be processed within 3-5 days'
                            ]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'System is unable to send request now',
                            ])->setStatusCode(403);
                        }
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Unable to get Admins',
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Unable to verify User'
                    ])->setStatusCode(403);
                }
            }
        } else {
            return redirect(config('adex.error_500'));
        }
    }



    public function buildWebsite(Request $request)
    {
        $explode_url = explode(',', config('adex.app_key'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            $validator = validator::make($request->all(), [
                'username' => 'required|max:25',
                'url' => 'required|url',
            ], [
                'url.url' => 'Invalid URL it must contain (https or www)',
                'url.required' => 'Your website URL is Needed For Verification'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 403
                ])->setStatusCode(403);
            } else {
                $check_me = [
                    'username' => $request->username,
                    'status' => 1
                ];
                $check_user = DB::table('user')->where($check_me);
                if ($check_user->count() == 1) {
                    $user = $check_user->get()[0];
                    $general = $this->general();
                    $date = $this->system_date();
                    $setting = $this->core();
                    $ref = $this->generate_ref('AFFLIATE_WEBSITE');
                    if (!empty($setting->affliate_price)) {
                        if ($user->bal > $setting->affliate_price) {
                            $verify = DB::table('message')->where('transid', $ref);
                            if ($verify->count() == 0) {
                                $check_request = DB::table('request')->where('transid', $ref);
                                if ($check_request->count() == 0) {
                                    $debit_user = $user->bal - $setting->affliate_price;
                                    $data = [
                                        'bal' => $debit_user,
                                    ];
                                    $where_user = [
                                        'username' => $user->username,
                                        'id' => $user->id
                                    ];
                                    $update_user = $this->updateData($data, 'user', $where_user);
                                    if ($update_user) {
                                        $insert_message = [
                                            'username' => $user->username,
                                            'amount' => $setting->affliate_price,
                                            'message' => 'Purchase An Affliate Website',
                                            'oldbal' => $user->bal,
                                            'newbal' => $debit_user,
                                            'adex_date' => $date,
                                            'transid' => $ref,
                                            'plan_status' => 1,
                                            'role' => 'WEBSITE'
                                        ];
                                        $this->inserting_data('message', $insert_message);
                                        $get_admins = DB::table('user')->where('status', '1')->where(function ($query) {
                                            $query->where('type', 'ADMIN')
                                                ->orWhere('type', 'CUSTOMER');
                                        });
                                        if ($get_admins->count() > 0) {
                                            foreach ($get_admins->get() as $send_admin) {
                                                $email_data = [
                                                    'name' => $user->name,
                                                    'email' => $send_admin->email,
                                                    'username' => $user->username,
                                                    'title' => 'AFFLIATE WEBSITE',
                                                    'sender_mail' => $general->app_email,
                                                    'user_email' => $user->email,
                                                    'app_name' => $general->app_name,
                                                    'website' => $request->url,
                                                    'date' => $date,
                                                    'transid' => $ref,
                                                    'app_phone' => $general->app_phone
                                                ];
                                                MailController::send_mail($email_data, 'email.affliate_request');
                                            }
                                            $insert_data = [
                                                'username' => $user->username,
                                                'date' => $date,
                                                'transid' => $ref,
                                                'status' => 0,
                                                'title' => 'AFFLIATE WEBSITE',
                                                'message' => $user->username . ', want to make an affliate website. Domain Url is (Account Debited)' . $request->url
                                            ];
                                            $insert = $this->inserting_data('request', $insert_data);
                                            if ($insert) {
                                                return response()->json([
                                                    'status' => 'success',
                                                    'message' => 'Your Request has been received and it will be processed within 3-5 days',
                                                ]);
                                            } else {
                                                return response()->json([
                                                    'status' => 403,
                                                    'message' => 'System is unable to send request now',
                                                ])->setStatusCode(403);
                                            }
                                        } else {
                                            return response()->json([
                                                'status' => 403,
                                                'message' => 'Unable to get Admins',
                                            ])->setStatusCode(403);
                                        }
                                    } else {
                                        return response()->json([
                                            'status' => 403,
                                            'message' => 'Service Currently Not Avialable For You Right Now'
                                        ])->setStatusCode(403);
                                    }
                                } else {
                                    return response()->json([
                                        'status' => 403,
                                        'message' => 'Please Try Again After Few Mins'
                                    ]);
                                }
                            } else {
                                return response()->json([
                                    'status' => 403,
                                    'message' => 'Please Try Again After Few Mins'
                                ]);
                            }
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Insufficient Account Fund Your Wallet And Try Again ~ ₦' . number_format($user->bal, 2)
                            ])->setStatusCode(403);
                        }
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'System Is Unable to Detect Price'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Unable to verify User',
                    ])->setStatusCode(403);
                }
            }
        } else {
            return redirect(config('adex.error_500'));
        }
    }
    public function AwufPackage(Request $request)
    {
        $explode_url = explode(',', config('adex.app_key'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!isset($request->id)) {
                return response()->json([
                    'message' => 'User ID Required',
                    'status' => 403
                ])->setStatusCode(403);
            } else {
                $check_me = [
                    'username' => $request->id,
                    'status' => 1
                ];
                $check_user = DB::table('user')->where($check_me);
                if ($check_user->count() == 1) {
                    $setting = $this->core();
                    $user = $check_user->first();
                    $ref = $this->generate_ref('AWUF_PACKAGE');
                    $date = $this->system_date();
                    if (!empty($setting->awuf_price)) {
                        if ($user->bal > $setting->awuf_price) {
                            $debit_user = $user->bal - $setting->awuf_price;
                            $credit_user = $debit_user + $setting->awuf_price;
                            if ($this->updateData(['bal' => $debit_user], 'user', ['username' => $user->username, 'id' => $user->id])) {
                                if (DB::table('message')->where('transid', $ref)->count() == 0) {
                                    $data = [
                                        'username' => $user->username,
                                        'amount' => $setting->awuf_price,
                                        'adex_date' => $date,
                                        'transid' => $ref,
                                        'plan_status' => 1,
                                        'newbal' => $debit_user,
                                        'oldbal' => $user->bal,
                                        'message' => 'Successfully Upgraded Your Account To AWUF PACKAGE',
                                        'role' => 'UPGRADE'
                                    ];
                                    if ($this->inserting_data('message', $data)) {
                                        $this->updateData(['type' => 'AWUF'], 'user', ['username' => $user->username, 'id' => $user->id]);
                                        return response()->json([
                                            'status' => 403,
                                            'message' => 'Account Upgraded To AWUF PACKAGE Successfully'
                                        ]);
                                    } else {
                                        $this->updateData(['bal' => $credit_user], 'user', ['username' => $user->username, 'id' => $user->id]);
                                        return response()->json([
                                            'status' => 403,
                                            'message' => 'Try Again Later'
                                        ])->setStatusCode(403);
                                    }
                                } else {
                                    $this->updateData(['bal' => $credit_user], 'user', ['username' => $user->username, 'id' => $user->id]);
                                    return response()->json([
                                        'status' => 403,
                                        'message' => 'Try Again Later'
                                    ])->setStatusCode(403);
                                }
                            } else {
                                return response()->json([
                                    'status' => 403,
                                    'message' => 'System Unavialable Right Now'
                                ])->setStatusCode(403);
                            }
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Insufficient Account, Fund Your Wallet And Try Again ~ ₦' . number_format($user->bal, 2)
                            ])->setStatusCode(403);
                        }
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'System is unable to Detect Price Right Now'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Unable to verify User',
                    ])->setStatusCode(403);
                }
            }
        } else {
            return redirect(config('adex.error_500'));
        }
    }

    //agent package

    public function AgentPackage(Request $request)
    {
        $explode_url = explode(',', config('adex.app_key'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!isset($request->id)) {
                return response()->json([
                    'message' => 'User ID Required',
                    'status' => 403
                ])->setStatusCode(403);
            } else {
                $check_me = [
                    'username' => $request->id,
                    'status' => 1
                ];
                $check_user = DB::table('user')->where($check_me);
                if ($check_user->count() == 1) {
                    $setting = $this->core();
                    $user = $check_user->first();
                    $ref = $this->generate_ref('AGENT_PACKAGE');
                    $date = $this->system_date();
                    if (!empty($setting->agent_price)) {
                        if ($user->bal > $setting->agent_price) {
                            $debit_user = $user->bal - $setting->agent_price;
                            $credit_user = $debit_user + $setting->agent_price;
                            if ($this->updateData(['bal' => $debit_user], 'user', ['username' => $user->username, 'id' => $user->id])) {
                                if (DB::table('message')->where('transid', $ref)->count() == 0) {
                                    $data = [
                                        'username' => $user->username,
                                        'amount' => $setting->agent_price,
                                        'adex_date' => $date,
                                        'transid' => $ref,
                                        'plan_status' => 1,
                                        'newbal' => $debit_user,
                                        'oldbal' => $user->bal,
                                        'message' => 'Successfully Upgraded Your Account To AGENT PACKAGE',
                                        'role' => 'UPGRADE'
                                    ];
                                    if ($this->inserting_data('message', $data)) {
                                        $this->updateData(['type' => 'AGENT'], 'user', ['username' => $user->username, 'id' => $user->id]);
                                        return response()->json([
                                            'status' => 403,
                                            'message' => 'Account Upgraded To AGENT PACKAGE Successfully'
                                        ]);
                                    } else {
                                        $this->updateData(['bal' => $credit_user], 'user', ['username' => $user->username, 'id' => $user->id]);
                                        return response()->json([
                                            'status' => 403,
                                            'message' => 'Try Again Later'
                                        ])->setStatusCode(403);
                                    }
                                } else {
                                    $this->updateData(['bal' => $credit_user], 'user', ['username' => $user->username, 'id' => $user->id]);
                                    return response()->json([
                                        'status' => 403,
                                        'message' => 'Try Again Later'
                                    ])->setStatusCode(403);
                                }
                            } else {
                                return response()->json([
                                    'status' => 403,
                                    'message' => 'System Unavialable Right Now'
                                ])->setStatusCode(403);
                            }
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Insufficient Account, Fund Your Wallet And Try Again ~ ₦' . number_format($user->bal, 2)
                            ])->setStatusCode(403);
                        }
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'System is unable to Detect Price Right Now'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Unable to verify User',
                    ])->setStatusCode(403);
                }
            }
        } else {
            return redirect(config('adex.error_500'));
        }
    }
    public function SystemNetwork(Request $request)
    {
        try {
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
            
            // Log for debugging
            \Log::info('SystemNetwork called', [
                'origin' => $origin,
                'originNormalized' => $originNormalized,
                'referer' => $referer,
                'host' => $host,
                'fullUrl' => $fullUrl,
                'isSameOrigin' => $isSameOrigin,
                'refererMatches' => $refererMatches,
                'allowedOrigins' => $allowedOrigins,
                'device_key_match' => config('adex.device_key') === $request->header('Authorization'),
                'authorization_header' => $request->header('Authorization') ? 'present' : 'missing'
            ]);
            
            // Allow if: origin matches, referer matches, same-origin request, or device key matches
            if (in_array($originNormalized, $allowedOrigins) 
                || $refererMatches 
                || $isSameOrigin 
                || config('adex.device_key') === $request->header('Authorization')
                || in_array($fullUrl, $allowedOrigins)) {
                
                $networks = DB::table('network')->select('network', 'network_vtu', 'network_share', 'network_sme', 'network_cg', 'network_g', 'plan_id', 'cash', 'data_card', 'recharge_card')->get();
                
                \Log::info('SystemNetwork success', ['network_count' => $networks->count()]);
                
                return response()->json([
                    'status' => 'success',
                    'network' => $networks
                ]);
            } else {
                \Log::warning('SystemNetwork origin mismatch', [
                    'origin' => $origin,
                    'originNormalized' => $originNormalized,
                    'referer' => $referer,
                    'fullUrl' => $fullUrl,
                    'allowedOrigins' => $allowedOrigins
                ]);
                return response()->json([
                    'status' => 403,
                    'message' => 'Unable to Authenticate System'
                ])->setStatusCode(403);
                ]);
                
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Origin not allowed'
                ], 403);
            }
        } catch (\Exception $e) {
            \Log::error('SystemNetwork error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'fail',
                'message' => 'An error occurred while fetching networks'
            ], 500);
        }
    }

    public function checkNetworkType(Request $type)
    {
        $allowedOrigins = array_filter(array_map('trim', explode(',', config('adex.app_key', ''))));
        $origin = $type->headers->get('origin');
        $originNormalized = rtrim($origin ?: '', '/');
        $referer = $type->headers->get('referer');
        $host = $type->getHost();
        $scheme = $type->getScheme();
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
            || config('adex.device_key') === $type->header('Authorization')
            || in_array($fullUrl, $allowedOrigins)) {
            if (!empty($type->id)) {
                if (isset($type->token)) {
                    $network = DB::table('network')->select('network', 'network_vtu', 'network_share', 'network_sme', 'network_cg', 'network_g', 'plan_id')->where('plan_id', $type->id)->first();
                    $user = DB::table('user')->where(['id' => $this->verifytoken($type->token), 'status' => 1]);
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


                        return response()->json([
                            'status' => 'success',
                            'network' => $network,
                            'price_vtu' => $airtime_discount->$check_for_vtu,
                            'price_sns' => $airtime_discount->$check_for_sns
                        ]);
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Reload Your Browser'
                        ])->setStatusCode(403);
                    }
                } else {
                    $network = DB::table('network')->select('network', 'network_vtu', 'network_share', 'network_sme', 'network_cg', 'network_g', 'plan_id')->where('plan_id', $type->id)->first();
                    return response()->json([
                        'status' => 'success',
                        'network' => $network,
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 403,
                    'message' => 'network plan id need'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }
    }

    public function DeleteUser(Request $request)
    {
        $explode_url = explode(',', config('adex.app_key'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() == 1) {
                    if (isset($request->username)) {
                        for ($i = 0; $i < count($request->username); $i++) {
                            $username = $request->username[$i];
                            $delete_user = DB::table('user')->where('username', $username);
                            if ($delete_user->count() > 0) {
                                $delete = DB::table('user')->where('username', $username)->delete();
                                DB::table('wallet_funding')->where('username', $username)->delete();
                            } else {
                                $delete = false;
                            }
                        }
                        if ($delete) {
                            return response()->json([
                                'status' => 'success',
                                'message' => 'Account Deleted Successfully'
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
                return redirect(config('adex.error_500'));
            }
        } else {
            return redirect(config('adex.error_500'));
        }
    }
    public function singleDelete(Request $request)
    {
        $explode_url = explode(',', config('adex.app_key'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() == 1) {
                    if (isset($request->username)) {
                        $check_user = DB::table('user')->where('username', $request->username);
                        if ($check_user->count() > 0) {
                            if (DB::table('user')->where('username', $request->username)->delete()) {
                                DB::table('wallet_funding')->where('username', $request->username)->delete();
                                return response()->json([
                                    'status' => 'success',
                                    'message' => 'Account Deleted Successfully'
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
                                'message' => 'User Not Found'
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
                return redirect(config('adex.error_500'));
            }
        } else {
            return redirect(config('adex.error_500'));
        }
    }
    public function UserNotif(Request $request)
    {
        $explode_url = explode(',', config('adex.app_key'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {

                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)]);
                if ($check_user->count() > 0) {
                    $adex = $check_user->first();
                    $adex_username = $adex->username;
                    // user request
                    $user_request = DB::table('notif')->where('username', $adex_username);
                    if ($user_request->count() > 0) {
                        foreach ($user_request->orderBy('id', 'desc')->get() as $adex) {
                            $select_user = DB::table('user')->where('username', $adex->username);
                            if ($select_user->count() > 0) {
                                $users = $select_user->first();
                                if ($users->profile_image !== null) {
                                    $profile_image[] = ['username' => $adex->username, 'id' => $adex->id, 'message' => $adex->message, 'date' => $adex->date, 'profile_image' => $users->profile_image, 'status' => $adex->adex];
                                } else {
                                    $profile_image[] = ['username' => $adex->username, 'id' => $adex->id, 'message' => $adex->message, 'date' => $adex->date, 'profile_image' => $users->username, 'status' => $adex->adex];
                                }
                            } else {
                                $profile_image[] = ['username' => $adex->username, 'id' => $adex->id, 'message' => $adex->message, 'date' => $adex->date, 'profile_image' => $adex->username, 'status' => $adex->adex];
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
            return redirect(config('adex.error_500'));
        }
    }
    public function ClearNotifUser(Request $request)
    {
        $explode_url = explode(',', config('adex.app_key'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {

                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)]);
                if ($check_user->count() > 0) {
                    $adex = $check_user->first();
                    $adex_username = $adex->username;
                    // user request
                    DB::table('notif')->where('username', $adex_username)->delete();
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
            return redirect(config('adex.error_500'));
        }
    }

    public function CableName(Request $request)
    {
        $explode_url = explode(',', config('adex.app_key'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            return response()->json([
                'status' => 'success',
                'cable' => DB::table('cable_result_lock')->first()
            ]);
        } else {
            return redirect(config('adex.error_500'));
        }
    }
    public function BillCal(Request $request)
    {
        $explode_url = explode(',', config('adex.app_key'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if ((isset($request->id)) && (!empty($request->id))) {
                if (is_numeric($request->id)) {
                    $bill_d = DB::table('bill_charge')->first();
                    if ($bill_d->direct == 1) {
                        $charges = $bill_d->bill;
                    } else {
                        $charges = ($request->id / 100) * $bill_d->bill;
                    }
                    return response()->json([
                        'status' => 'suucess',
                        'charges' => $charges
                    ]);
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'invalid amount'
                    ])->setStatusCode(403);
                }
            }
        } else {
            return redirect(config('adex.error_500'));
        }
    }
    public function DiscoList()
    {
        return response()->json([
            'status' => 'success',
            'bill' => DB::table('bill_plan')->where('plan_status', 1)->select('plan_id', 'disco_name')->get()
        ]);
    }
    public function CashNumber()
    {
        return response()->json([
            'numbers' => DB::table('cash_discount')->select('mtn_number', 'glo_number', 'mobile_number', 'airtel_number')->first()
        ]);
    }
    public function AirtimeCash(Request $request)
    {
        $explode_url = explode(',', config('adex.app_key'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->amount)) {
                if (!empty($request->network)) {

                    if ($request->network == '9MOBILE') {
                        $network_name = 'mobile';
                    } else {
                        $network_name = strtolower($request->network);
                    }
                    $system_admin = DB::table('cash_discount')->first();
                    $credit = ($request->amount / 100) * $system_admin->$network_name;

                    return response()->json([
                        'amount' => $credit,
                        'status' => 'success'
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Network Required'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([])->setStatusCode(403);
            }
        } else {
            return redirect(config('adex.error_500'));
        }
    }
    public function BulksmsCal(Request $request)
    {
        $explode_url = explode(',', config('adex.app_key'));
        $origin = $request->headers->get('origin');
        if (!$origin || in_array($origin, $explode_url)) {
            return response()->json([
                'amount' => $this->core()->bulk_sms
            ]);
        } else {
            return redirect(config('adex.error_500'));
        }
    }
    public function ResultPrice(Request $request)
    {
        $explode_url = explode(',', config('adex.app_key'));
        $origin = $request->headers->get('origin');
        if (!$origin || in_array($origin, $explode_url)) {
            return response()->json([
                'price' => DB::table('result_charge')->first()
            ]);
        } else {
            return redirect(config('adex.error_500'));
        }
    }
}
