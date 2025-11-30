<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class  PlanController extends Controller
{

    public function DataPlan(Request $request)
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
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)]);
                if ($check_user->count() == 1) {
                    $adex = $check_user->first();

                    // validate form
                    $main_validator = validator::make($request->all(), [
                        'network' => 'required',
                        'network_type' => 'required',
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
                        $get_network = DB::table('network')->where('plan_id', $request->network)->first();
                        if (!$get_network) {
                            return response()->json([
                                'status' => 'fail',
                                'message' => 'Network not found'
                            ])->setStatusCode(404);
                        }
                        
                        // Check if network type is locked
                        $isLocked = false;
                        if ($request->network_type == 'SME') {
                            $isLocked = $get_network->network_sme == 0;
                        } elseif ($request->network_type == 'COOPERATE GIFTING' || $request->network_type == 'CG') {
                            $isLocked = $get_network->network_cg == 0;
                        } elseif ($request->network_type == 'GIFTING' || $request->network_type == 'G') {
                            $isLocked = $get_network->network_g == 0;
                        }
                        
                        if ($isLocked) {
                            return response()->json([
                                'status' => 'fail',
                                'message' => 'This network type is currently locked',
                                'plan' => []
                            ]);
                        }
                        
                        $all_plan = DB::table('data_plan')->where(['network' => $get_network->network, 'plan_type' => $request->network_type, 'plan_status' => 1]);
                        $data_plan = [];
                        if ($all_plan->count() > 0) {
                            foreach ($all_plan->get() as $adex => $plan) {
                                $data_plan[] =  ['name' => $plan->plan_name . $plan->plan_size . ' ' . $plan->plan_type . ' = ₦' . number_format($plan->$user_type, 2) . ' ' . $plan->plan_day, 'plan_id' => $plan->plan_id, 'amount' => $plan->$user_type];
                            }
                        }
                        return response()->json([
                            'status' => 'success',
                            'plan' => $data_plan
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return redirect(config('adex.error_500', '/500'));
            }
        } else {
            return redirect(config('adex.error_500', '/500'));
        }
    }
    public function CablePlan(Request $request)
    {
        $allowedOrigins = array_filter(array_map('trim', explode(',', config('adex.app_key', ''))));
        $origin = $request->headers->get('origin');
        $originNormalized = rtrim($origin ?: '', '/');
        
        if (in_array($originNormalized, $allowedOrigins) || config('adex.device_key') === $request->header('Authorization')) {
            if (isset($request->id)) {
                $cable_name = DB::table('cable_id')->where('plan_id', $request->id)->first();
                return response()->json([
                    'status' => 'suucess',
                    'plan' => DB::table('cable_plan')->where(['cable_name' => $cable_name->cable_name, 'plan_status' => 1])->select('cable_name', 'plan_name', 'plan_price', 'plan_id')->get()
                ]);
            }
        } else {
            return redirect(config('adex.error_500', '/500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function CableCharges(Request $request)
    {
        $allowedOrigins = array_filter(array_map('trim', explode(',', config('adex.app_key', ''))));
        $origin = $request->headers->get('origin');
        $originNormalized = rtrim($origin ?: '', '/');
        
        if (in_array($originNormalized, $allowedOrigins) || config('adex.device_key') === $request->header('Authorization')) {
            if (isset($request->id)) {
                if (DB::table('cable_plan')->where('plan_id', $request->id)->count() == 1) {
                    $cable = DB::table('cable_plan')->where('plan_id', $request->id)->first();
                    $amount = $cable->plan_price;
                    $cable_name = strtolower($cable->cable_name);
                    $cable_setting = DB::table('cable_charge')->first();
                    if ($cable_setting->direct == 1) {
                        $charges = $cable_setting->$cable_name;
                    } else {
                        $charges = ($amount / 100) * $cable_setting->$cable_name;
                    }
                    return response()->json([
                        'status' => 'suucess',
                        'amount' => $amount,
                        'charges' => $charges
                    ]);
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Unable to calculate'
                    ])->setStatusCode(403);
                }
            }
        } else {
            return redirect(config('adex.error_500', '/500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function DataList(Request $request)
    {
        $allowedOrigins = array_filter(array_map('trim', explode(',', config('adex.app_key', ''))));
        $origin = $request->headers->get('origin');
        $originNormalized = rtrim($origin ?: '', '/');
        
        if (in_array($originNormalized, $allowedOrigins) || config('adex.device_key') === $request->header('Authorization')) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)]);
                if ($check_user->count() == 1) {
                    $adex = $check_user->first();
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


                    // Get network lock status
                    $networks = DB::table('network')->get()->keyBy('network');
                    
                    $all_plan = DB::table('data_plan')->where(['plan_status' => 1]);
                    $data_plan = [];
                    if ($all_plan->count() > 0) {
                        foreach ($all_plan->get() as $adex => $plan) {
                            $network = $networks->get($plan->network);
                            if ($network) {
                                // Check if this plan type is locked
                                $isLocked = false;
                                if ($plan->plan_type == 'SME') {
                                    $isLocked = $network->network_sme == 0;
                                } elseif ($plan->plan_type == 'COOPERATE GIFTING' || $plan->plan_type == 'CG') {
                                    $isLocked = $network->network_cg == 0;
                                } elseif ($plan->plan_type == 'GIFTING' || $plan->plan_type == 'G') {
                                    $isLocked = $network->network_g == 0;
                                }
                                
                                // Only include plan if not locked
                                if (!$isLocked) {
                                    $data_plan[] =  ['plan_name' => $plan->plan_name . $plan->plan_size, 'plan_id' => $plan->plan_id, 'amount' => number_format($plan->$user_type, 2), 'plan_type' => $plan->plan_type, 'plan_day' => $plan->plan_day, 'network' => $plan->network];
                                }
                            }
                        }
                    }
                    return response()->json([
                        'status' => 'success',
                        'plan' => $data_plan
                    ]);
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return redirect(config('adex.error_500', '/500'));
                return response()->json([
                    'status' => 403,
                    'message' => 'Unable to Authenticate System'
                ])->setStatusCode(403);
            }
        } else {
            return redirect(config('adex.error_500', '/500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function CableList(Request $request)
    {
        $allowedOrigins = array_filter(array_map('trim', explode(',', config('adex.app_key', ''))));
        $origin = $request->headers->get('origin');
        $originNormalized = rtrim($origin ?: '', '/');
        
        if (in_array($originNormalized, $allowedOrigins) || config('adex.device_key') === $request->header('Authorization')) {




            return response()->json([
                'status' => 'success',
                'plan' => DB::table('cable_plan')->where('plan_status', 1)->select('cable_name', 'plan_name', 'plan_price', 'plan_id')->get()
            ]);
        } else {
            return redirect(config('adex.error_500', '/500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function DiscoList(Request $request)
    {
        $allowedOrigins = array_filter(array_map('trim', explode(',', config('adex.app_key', ''))));
        $origin = $request->headers->get('origin');
        $originNormalized = rtrim($origin ?: '', '/');
        
        if (in_array($originNormalized, $allowedOrigins) || config('adex.device_key') === $request->header('Authorization')) {




            return response()->json([
                'status' => 'success',
                'plan' => DB::table('bill_plan')->where('plan_status', 1)->select('disco_name', 'plan_id')->get()
            ]);
        } else {
            return redirect(config('adex.error_500', '/500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }

    public function ExamList(Request $request)
    {
        $allowedOrigins = array_filter(array_map('trim', explode(',', config('adex.app_key', ''))));
        $origin = $request->headers->get('origin');
        $originNormalized = rtrim($origin ?: '', '/');
        
        if (in_array($originNormalized, $allowedOrigins) || config('adex.device_key') === $request->header('Authorization')) {
            $exam_list = [];
            $exam_id = DB::table('exam_id')->get();
            $exam_price = DB::table('result_charge')->first();
            foreach ($exam_id as $exam) {
                if ($exam->exam_name == 'WAEC') {
                    $exam_list[] = ['exam_name' => $exam->exam_name, 'plan_id' => $exam->plan_id, 'amount' => '₦' . number_format($exam_price->waec, 2)];
                }
                if ($exam->exam_name == 'NECO') {
                    $exam_list[] = ['exam_name' => $exam->exam_name, 'plan_id' => $exam->plan_id, 'amount' => '₦' . number_format($exam_price->neco, 2)];
                }

                if ($exam->exam_name == 'NABTEB') {
                    $exam_list[] = ['exam_name' => $exam->exam_name, 'plan_id' => $exam->plan_id, 'amount' => '₦' . number_format($exam_price->nabteb, 2)];
                }
            }
            return response()->json([
                'status' => 'success',
                'plan' => $exam_list
            ]);
        } else {
            return redirect(config('adex.error_500', '/500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function HomeData(Request $request)
    {
        $allowedOrigins = array_filter(array_map('trim', explode(',', config('adex.app_key', ''))));
        $origin = $request->headers->get('origin');
        $originNormalized = rtrim($origin ?: '', '/');
        
        if (in_array($originNormalized, $allowedOrigins) || config('adex.device_key') === $request->header('Authorization')) {
            // Get network lock status
            $networks = DB::table('network')->get()->keyBy('network');
            
            // Helper function to filter plans by lock status for a network
            $filterNetworkPlans = function($networkName) use ($networks) {
                $network = $networks->get($networkName);
                if (!$network) return collect([]);
                
                $allPlans = DB::table('data_plan')
                    ->where(['network' => $networkName, 'plan_status' => 1])
                    ->select('plan_name', 'network', 'plan_size', 'plan_day', 'smart', 'plan_type')
                    ->orderBy('smart', 'asc')
                    ->get();
                
                // Filter out locked plan types
                return $allPlans->filter(function($plan) use ($network) {
                    if ($plan->plan_type == 'SME') {
                        return $network->network_sme == 1;
                    } elseif ($plan->plan_type == 'COOPERATE GIFTING' || $plan->plan_type == 'CG') {
                        return $network->network_cg == 1;
                    } elseif ($plan->plan_type == 'GIFTING' || $plan->plan_type == 'G') {
                        return $network->network_g == 1;
                    }
                    // For other plan types (like DIRECT), include them
                    return true;
                })->map(function($plan) {
                    // Remove plan_type from response to match original format
                    return (object)[
                        'plan_name' => $plan->plan_name,
                        'network' => $plan->network,
                        'plan_size' => $plan->plan_size,
                        'plan_day' => $plan->plan_day,
                        'smart' => $plan->smart
                    ];
                });
            };
            
            return response()->json([
                'status' => 'success',
                'mtn' => $filterNetworkPlans('MTN')->values(),
                'glo' => $filterNetworkPlans('GLO')->values(),
                'airtel' => $filterNetworkPlans('AIRTEL')->values(),
                'mobile' => $filterNetworkPlans('9MOBILE')->values()
            ]);
        } else {
            return redirect(config('adex.error_500', '/500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }

    public function DataCard(Request $request)
    {
        $allowedOrigins = array_filter(array_map('trim', explode(',', config('adex.app_key', ''))));
        $origin = $request->headers->get('origin');
        $originNormalized = rtrim($origin ?: '', '/');
        
        if (in_array($originNormalized, $allowedOrigins) || config('adex.device_key') === $request->header('Authorization')) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)]);
                if ($check_user->count() == 1) {
                    $adex = $check_user->first();
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


                    $all_plan = DB::table('data_card_plan')->where(['plan_status' => 1]);
                    if ($all_plan->count() > 0) {
                        foreach ($all_plan->get() as $adex => $plan) {
                            $data_plan[] =  ['plan_name' => $plan->name . $plan->plan_size, 'plan_id' => $plan->plan_id, 'amount' => number_format($plan->$user_type, 2), 'plan_type' => $plan->plan_type, 'plan_day' => $plan->plan_day, 'network' => $plan->network, 'load_pin' => $plan->load_pin, 'check_balance' => $plan->check_balance];;
                        }
                    } else {
                        $data_plan = [];
                    }
                    return response()->json([
                        'status' => 'success',
                        'plan' => $data_plan
                    ]);
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return redirect(config('adex.error_500', '/500'));
                return response()->json([
                    'status' => 403,
                    'message' => 'Unable to Authenticate System'
                ])->setStatusCode(403);
            }
        } else {
            return redirect(config('adex.error_500', '/500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }

    public function RechargeCard(Request $request)
    {
        $allowedOrigins = array_filter(array_map('trim', explode(',', config('adex.app_key', ''))));
        $origin = $request->headers->get('origin');
        $originNormalized = rtrim($origin ?: '', '/');
        
        if (in_array($originNormalized, $allowedOrigins) || config('adex.device_key') === $request->header('Authorization')) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)]);
                if ($check_user->count() == 1) {
                    $adex = $check_user->first();
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


                    $all_plan = DB::table('recharge_card_plan')->where(['plan_status' => 1]);
                    if ($all_plan->count() > 0) {
                        foreach ($all_plan->get() as $adex => $plan) {
                            $data_plan[] =  ['name' => $plan->name, 'plan_id' => $plan->plan_id, 'amount' => number_format($plan->$user_type, 2),  'network' => $plan->network, 'load_pin' => $plan->load_pin, 'check_balance' => $plan->check_balance];;
                        }
                    } else {
                        $data_plan = [];
                    }
                    return response()->json([
                        'status' => 'success',
                        'plan' => $data_plan
                    ]);
                } else {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Not Authorised'
                    ])->setStatusCode(403);
                }
            } else {
                return redirect(config('adex.error_500', '/500'));
                return response()->json([
                    'status' => 403,
                    'message' => 'Unable to Authenticate System'
                ])->setStatusCode(403);
            }
        } else {
            return redirect(config('adex.error_500', '/500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
}
