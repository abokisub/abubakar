<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class  Selection extends Controller
{
    public function DataSel(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    $sel = DB::table('data_sel')->first();
                    $select_adex = DB::table('sel')->select('name', 'key')->where('data', 1)->get();
                    return response()->json([
                        'mtn_sme' => $sel->mtn_sme,
                        'airtel_sme' => $sel->airtel_sme,
                        'glo_sme' => $sel->glo_sme,
                        'mobile_sme' => $sel->mobile_sme,

                        'mtn_cg' => $sel->mtn_cg,
                        'airtel_cg' => $sel->airtel_cg,
                        'glo_cg' => $sel->glo_cg,
                        'mobile_cg' => $sel->mobile_cg,

                        'mtn_g' => $sel->mtn_g,
                        'airtel_g' => $sel->airtel_g,
                        'glo_g' => $sel->glo_g,
                        'mobile_g' => $sel->mobile_g,

                        'adex_code' => $select_adex,
                    ]);
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
            return redirect(env('ERROR_500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function AirtimeSel(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    $sel = DB::table('airtime_sel')->first();
                    $select_adex = DB::table('sel')->select('name', 'key')->where('airtime', 1)->get();
                    return response()->json([
                        'airtime' => $sel,
                        'adex_code' => $select_adex,
                    ]);
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
            return redirect(env('ERROR_500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function CableSel(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    $sel = DB::table('cable_sel')->first();
                    $select_adex = DB::table('sel')->select('name', 'key')->where('cable', 1)->get();
                    return response()->json([
                        'cable' => $sel,
                        'adex_code' => $select_adex,
                    ]);
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
            return redirect(env('ERROR_500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function BulksmsSel(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    $sel = DB::table('bulksms_sel')->first();
                    $select_adex = DB::table('sel')->select('name', 'key')->where('bulksms', 1)->get();
                    return response()->json([
                        'bulksms' => $sel,
                        'adex_code' => $select_adex,
                    ]);
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
            return redirect(env('ERROR_500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function BillSel(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    $sel = DB::table('bill_sel')->first();
                    $select_adex = DB::table('sel')->select('name', 'key')->where('bill', 1)->get();
                    return response()->json([
                        'bill' => $sel,
                        'adex_code' => $select_adex,
                    ]);
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
            return redirect(env('ERROR_500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function ResultSel(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    $sel = DB::table('exam_sel')->first();
                    $select_adex = DB::table('sel')->select('name', 'key')->where('result', 1)->get();
                    return response()->json([
                        'exam' => $sel,
                        'adex_code' => $select_adex,
                    ]);
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
            return redirect(env('ERROR_500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function DataCard(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    $sel = DB::table('data_card_sel')->first();
                    $select_adex = DB::table('sel')->select('name', 'key')->where('data_card', 1)->get();
                    return response()->json([
                        'data_card' => $sel,
                        'adex_code' => $select_adex,
                    ]);
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
            return redirect(env('ERROR_500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }

    public function RechargeCard(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    $sel = DB::table('recharge_card_sel')->first();
                    $select_adex = DB::table('sel')->select('name', 'key')->where('recharge_card', 1)->get();
                    return response()->json([
                        'recharge_card' => $sel,
                        'adex_code' => $select_adex,
                    ]);
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
            return redirect(env('ERROR_500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
}
