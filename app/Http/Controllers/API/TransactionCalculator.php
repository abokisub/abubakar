<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class  TransactionCalculator extends Controller
{

    public function Admin(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN');
                });
                if ($check_user->count() > 0) {
                    // all here
                    if ($request->status == 'TODAY') {
                        $data_trans = DB::table('data')->whereDate('plan_date',  Carbon::now("Africa/Lagos"))->where(['plan_status' => 1])->get();
                        $airtime_trans = DB::table('airtime')->whereDate('plan_date',  Carbon::now("Africa/Lagos"))->where(['plan_status' => 1])->get();
                        $cable_trans = DB::table('cable')->whereDate('plan_date',  Carbon::now("Africa/Lagos"))->where(['plan_status' => 1])->get();
                        $exam_trans = DB::table('exam')->whereDate('plan_date',  Carbon::now("Africa/Lagos"))->where(['plan_status' => 1])->get();
                        $bulksms_trans = DB::table('bulksms')->whereDate('plan_date',  Carbon::now("Africa/Lagos"))->where(['plan_status' => 1])->get();
                        $deposit_trans = DB::table('deposit')->whereDate('date',  Carbon::now("Africa/Lagos"))->where(['status' => 1])->get();
                        $spend_trans = DB::table('message')->where(function ($query) {
                            $query->where('role', '!=', 'credit');
                            $query->where('plan_status', '!=', 2);
                        })->whereDate('adex_date',  Carbon::now("Africa/Lagos"))->where(['plan_status' => 1])->get();
                        $cash_trans = DB::table('cash')->whereDate('plan_date',  Carbon::now("Africa/Lagos"))->where(['plan_status' => 1])->get();
                        $bill_trans = DB::table('bill')->whereDate('plan_date',  Carbon::now("Africa/Lagos"))->where(['plan_status' => 1])->get();
                    } else if ($request->status == '7DAYS') {
                        $data_trans = DB::table('data')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(7))->where(['plan_status' => 1])->get();
                        $airtime_trans = DB::table('airtime')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(7))->where(['plan_status' => 1])->get();
                        $cable_trans = DB::table('cable')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(7))->where(['plan_status' => 1])->get();
                        $exam_trans = DB::table('exam')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(7))->where(['plan_status' => 1])->get();
                        $bulksms_trans = DB::table('bulksms')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(7))->where(['plan_status' => 1])->get();
                        $deposit_trans = DB::table('deposit')->whereDate('date', '>',  Carbon::now("Africa/Lagos")->subDays(7))->where(['status' => 1])->get();
                        $spend_trans = DB::table('message')->where(function ($query) {
                            $query->where('role', '!=', 'credit');
                            $query->where('plan_status', '!=', 2);
                        })->whereDate('adex_date', '>',  Carbon::now("Africa/Lagos")->subDays(7))->where(['plan_status' => 1])->get();
                        $cash_trans = DB::table('cash')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(7))->where(['plan_status' => 1])->get();
                        $bill_trans = DB::table('bill')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(7))->where(['plan_status' => 1])->get();
                    } else if ($request->status == '30DAYS') {
                        $data_trans = DB::table('data')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(30))->where(['plan_status' => 1])->get();
                        $airtime_trans = DB::table('airtime')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(30))->where(['plan_status' => 1])->get();
                        $cable_trans = DB::table('cable')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(30))->where(['plan_status' => 1])->get();
                        $exam_trans = DB::table('exam')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(30))->where(['plan_status' => 1])->get();
                        $bulksms_trans = DB::table('bulksms')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(30))->where(['plan_status' => 1])->get();
                        $deposit_trans = DB::table('deposit')->whereDate('date', '>',  Carbon::now("Africa/Lagos")->subDays(30))->where(['status' => 1])->get();
                        $spend_trans = DB::table('message')->where(function ($query) {
                            $query->where('role', '!=', 'credit');
                            $query->where('plan_status', '!=', 2);
                        })->whereDate('adex_date', '>',  Carbon::now("Africa/Lagos")->subDays(30))->where(['plan_status' => 1])->get();
                        $cash_trans = DB::table('cash')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(30))->where(['plan_status' => 1])->get();
                        $bill_trans = DB::table('bill')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(30))->where(['plan_status' => 1])->get();
                    } else if ($request->status == 'ALL TIME') {
                        $data_trans = DB::table('data')->where(['plan_status' => 1])->get();
                        $airtime_trans = DB::table('airtime')->where(['plan_status' => 1])->get();
                        $cable_trans = DB::table('cable')->where(['plan_status' => 1])->get();
                        $exam_trans = DB::table('exam')->where(['plan_status' => 1])->get();
                        $bulksms_trans = DB::table('bulksms')->where(['plan_status' => 1])->get();
                        $deposit_trans = DB::table('deposit')->where(['status' => 1])->get();
                        $spend_trans = DB::table('message')->where(function ($query) {
                            $query->where('role', '!=', 'credit');
                            $query->where('plan_status', '!=', 2);
                        })->where(['plan_status' => 1])->get();
                        $cash_trans = DB::table('cash')->where(['plan_status' => 1])->get();
                        $bill_trans = DB::table('bill')->where(['plan_status' => 1])->get();
                    } else if ($request->status == 'CUSTOM USER') {
                        if ((isset($request->from)) and isset($request->to)) {
                            if (!empty($request->username)) {
                                if ((!empty($request->from)) and !empty($request->to)) {
                                    if (DB::table('user')->where(['username' => $request->username])->count() == 1) {
                                        $start_date = Carbon::parse($request->from . ' 00:00:00')->toDateTimeString();
                                        $end_date = Carbon::parse($request->to . ' 23:59:59')->toDateTimeString();
                                        $data_trans = DB::table('data')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1, 'username' => $request->username])->get();
                                        $airtime_trans = DB::table('airtime')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1, 'username' => $request->username])->get();
                                        $cable_trans = DB::table('cable')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1, 'username' => $request->username])->get();
                                        $exam_trans = DB::table('exam')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1, 'username' => $request->username])->get();
                                        $bulksms_trans = DB::table('bulksms')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1, 'username' => $request->username])->get();
                                        $deposit_trans = DB::table('deposit')->whereBetween('date', [$start_date, $end_date])->where(['status' => 1, 'username' => $request->username])->get();
                                        $spend_trans = DB::table('message')->where(function ($query) {
                                            $query->where('role', '!=', 'credit');
                                            $query->where('plan_status', '!=', 2);
                                        })->whereBetween('adex_date', [$start_date, $end_date])->where(['plan_status' => 1, 'username' => $request->username])->get();
                                        $cash_trans = DB::table('cash')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1, 'username' => $request->username])->get();
                                        $bill_trans = DB::table('bill')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1, 'username' => $request->username])->get();
                                    } else {
                                        return response()->json([
                                            'message' => 'Invalid User Username'
                                        ])->setStatusCode(403);
                                    }
                                } else {
                                    return response()->json([
                                        'message' => 'start date and end date required'
                                    ])->setStatusCode(403);
                                }
                            } else {
                                return response()->json([
                                    'messsage' => ' Username Required'
                                ])->setStatusCode(403);
                            }
                        } else {
                            return response()->json([
                                'message' => 'start date and end date required'
                            ])->setStatusCode(403);
                        }
                    } else {
                        if ((isset($request->from)) and isset($request->to)) {
                            if ((!empty($request->from)) and !empty($request->to)) {
                                $start_date = Carbon::parse($request->from . ' 00:00:00')->toDateTimeString();
                                $end_date = Carbon::parse($request->to . ' 23:59:59')->toDateTimeString();
                                $data_trans = DB::table('data')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1])->get();
                                $airtime_trans = DB::table('airtime')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1])->get();
                                $cable_trans = DB::table('cable')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1])->get();
                                $exam_trans = DB::table('exam')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1])->get();
                                $bulksms_trans = DB::table('bulksms')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1])->get();
                                $deposit_trans = DB::table('deposit')->whereBetween('date', [$start_date, $end_date])->where(['status' => 1])->get();
                                $spend_trans = DB::table('message')->where(function ($query) {
                                    $query->where('role', '!=', 'credit');
                                    $query->where('plan_status', '!=', 2);
                                })->whereBetween('adex_date', [$start_date, $end_date])->where(['plan_status' => 1])->get();
                                $cash_trans = DB::table('cash')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1])->get();
                                $bill_trans = DB::table('bill')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1])->get();
                            } else {
                                return response()->json([
                                    'message' => 'start date and end date required'
                                ])->setStatusCode(403);
                            }
                        } else {
                            return response()->json([
                                'message' => 'start date and end date required'
                            ])->setStatusCode(403);
                        }
                    }
                    // FOR DATA
                    $mtn_g = 0;
                    $mtn_sme = 0;
                    $mtn_cg = 0;
                    $mtn_g_bal = 0;
                    $mtn_cg_bal = 0;
                    $mtn_sme_bal = 0;

                    $airtel_g = 0;
                    $airtel_sme = 0;
                    $airtel_cg = 0;
                    $airtel_g_bal = 0;
                    $airtel_cg_bal = 0;
                    $airtel_sme_bal = 0;

                    $glo_g = 0;
                    $glo_sme = 0;
                    $glo_cg = 0;
                    $glo_g_bal = 0;
                    $glo_cg_bal = 0;
                    $glo_sme_bal = 0;

                    $mobile_g = 0;
                    $mobile_sme = 0;
                    $mobile_cg = 0;
                    $mobile_g_bal = 0;
                    $mobile_cg_bal = 0;
                    $mobile_sme_bal = 0;
                    foreach ($data_trans as $data) {
                        if ($data->network == 'MTN' and $data->network_type == 'GIFTING') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $mtn_g += $gb;
                            $mtn_g_bal  += $data->amount;
                        } else if ($data->network == 'MTN' and $data->network_type == 'SME') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $mtn_sme += $gb;
                            $mtn_sme_bal  += $data->amount;
                        } else if ($data->network == 'MTN' and $data->network_type == 'COOPERATE GIFTING') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $mtn_cg += $gb;
                            $mtn_cg_bal  += $data->amount;
                        } else if ($data->network == 'AIRTEL' and $data->network_type == 'GIFTING') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $airtel_g += $gb;
                            $airtel_g_bal  += $data->amount;
                        } else if ($data->network == 'AIRTEL' and $data->network_type == 'SME') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $airtel_sme += $gb;
                            $airtel_sme_bal  += $data->amount;
                        } else if ($data->network == 'AIRTEL' and $data->network_type == 'COOPERATE GIFTING') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $airtel_cg += $gb;
                            $airtel_cg_bal  += $data->amount;
                        } else if ($data->network == 'GLO' and $data->network_type == 'GIFTING') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $glo_g += $gb;
                            $glo_g_bal  += $data->amount;
                        } else if ($data->network == 'GLO' and $data->network_type == 'SME') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $glo_sme += $gb;
                            $glo_sme_bal  += $data->amount;
                        } else if ($data->network == 'GLO' and $data->network_type == 'COOPERATE GIFTING') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $glo_cg += $gb;
                            $glo_cg_bal  += $data->amount;
                        } else if ($data->network == '9MOBILE' and $data->network_type == 'GIFTING') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $mobile_g += $gb;
                            $mobile_g_bal  += $data->amount;
                        } else if ($data->network == '9MOBILE' and $data->network_type == 'SME') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $mobile_sme += $gb;
                            $mobile_sme_bal  += $data->amount;
                        } else if ($data->network == '9MOBILE' and $data->network_type == 'COOPERATE GIFTING') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $mobile_cg += $gb;
                            $mobile_cg_bal  += $data->amount;
                        }
                    }


                    // airtime
                    $mtn_vtu = 0;
                    $mtn_vtu_d = 0;
                    $mtn_sns = 0;
                    $mtn_sns_d = 0;

                    $airtel_vtu = 0;
                    $airtel_vtu_d = 0;
                    $airtel_sns = 0;
                    $airtel_sns_d = 0;

                    $glo_vtu = 0;
                    $glo_vtu_d = 0;
                    $glo_sns = 0;
                    $glo_sns_d = 0;

                    $mobile_vtu = 0;
                    $mobile_vtu_d = 0;
                    $mobile_sns = 0;
                    $mobile_sns_d = 0;
                    foreach ($airtime_trans as $airtime) {
                        if ($airtime->network == 'MTN' and $airtime->network_type == 'VTU') {
                            $mtn_vtu += $airtime->amount;
                            $mtn_vtu_d += $airtime->discount;
                        }
                        if ($airtime->network == 'MTN' and $airtime->network_type == 'SNS') {
                            $mtn_sns += $airtime->amount;
                            $mtn_sns_d += $airtime->discount;
                        }

                        if ($airtime->network == 'AIRTEL' and $airtime->network_type == 'VTU') {
                            $airtel_vtu += $airtime->amount;
                            $airtel_vtu_d += $airtime->discount;
                        }
                        if ($airtime->network == 'AIRTEL' and $airtime->network_type == 'SNS') {
                            $airtel_sns += $airtime->amount;
                            $airtel_sns_d += $airtime->discount;
                        }

                        if ($airtime->network == 'GLO' and $airtime->network_type == 'VTU') {
                            $glo_vtu += $airtime->amount;
                            $glo_vtu_d += $airtime->discount;
                        }
                        if ($airtime->network == 'GLO' and $airtime->network_type == 'SNS') {
                            $glo_sns += $airtime->amount;
                            $glo_sns_d += $airtime->discount;
                        }

                        if ($airtime->network == '9MOBILE' and $airtime->network_type == 'VTU') {
                            $mobile_vtu += $airtime->amount;
                            $mobile_vtu_d += $airtime->discount;
                        }
                        if ($airtime->network == '9MOBILE' and $airtime->network_type == 'SNS') {
                            $mobile_sns += $airtime->amount;
                            $mobile_sns_d += $airtime->discount;
                        }
                    }
                    // cable
                    $dstv = 0;
                    $dstv_c = 0;
                    $gotv = 0;
                    $gotv_c = 0;
                    $startime = 0;
                    $startime_c = 0;
                    foreach ($cable_trans as $cable) {
                        if ($cable->cable_name == 'DSTV') {
                            $dstv += $cable->amount;
                            $dstv_c += $cable->charges;
                        }
                        if ($cable->cable_name == 'GOTV') {
                            $gotv += $cable->amount;
                            $gotv_c += $cable->charges;
                        }
                        if ($cable->cable_name == 'STARTIME') {
                            $startime += $cable->amount;
                            $startime_c += $cable->charges;
                        }
                    }
                    // exam
                    $waec = 0;
                    $waec_q = 0;
                    $neco = 0;
                    $neco_q = 0;
                    $nabteb = 0;
                    $nabteb_q = 0;
                    foreach ($exam_trans as $exam) {
                        if ($exam->exam_name == 'WAEC') {
                            $waec += $exam->amount;
                            $waec_q += $exam->quantity;
                        }
                        if ($exam->exam_name == 'NECO') {
                            $neco += $exam->amount;
                            $neco_q += $exam->quantity;
                        }
                        if ($exam->exam_name == 'NABTEB') {
                            $nabteb += $exam->amount;
                            $nabteb_q += $exam->quantity;
                        }
                    }
                    // bulksms
                    $bulksms = 0;
                    foreach ($bulksms_trans as $bulk) {
                        $bulksms += $bulk->amount;
                    }
                    // bill
                    $bill = 0;
                    foreach ($bill_trans as $d) {
                        $bill += $d->amount;
                    }
                    // airtime 2 cash
                    $cash = 0;
                    $cash_pay = 0;
                    foreach ($cash_trans as $d) {
                        $cash += $d->amount;
                        $cash_pay += $d->amount_credit;
                    }
                    // deposit
                    $deposit_amount = 0;
                    $deposit_charges = 0;
                    foreach ($deposit_trans as $deposit) {
                        $deposit_amount += $deposit->amount;
                        $deposit_charges += $deposit->charges;
                    }
                    $money_spent = 0;
                    foreach ($spend_trans as $spend) {
                        $money_spent += $spend->amount;
                    }
                    $adex_in = $deposit_amount;
                    $adex_out = $money_spent;
                    $total_m = $adex_in + $adex_out;
                    if ($total_m != 0) {
                        $adex_in_trans = ($adex_in / $total_m) * 100;
                        $adex_out_trans = ($adex_out / $total_m) * 100;
                    } else {
                        $adex_in_trans = 0;
                        $adex_out_trans = 0;
                    }

                    $calculate_mtn_cg = '0GB';
                    $calculate_mtn_g = '0GB';
                    $calculate_mtn_sme = '0GB';

                    $calculate_airtel_cg = '0GB';
                    $calculate_airtel_g = '0GB';
                    $calculate_airtel_sme = '0GB';

                    $calculate_glo_cg = '0GB';
                    $calculate_glo_g = '0GB';
                    $calculate_glo_sme = '0GB';

                    $calculate_mobile_cg = '0GB';
                    $calculate_mobile_g = '0GB';
                    $calculate_mobile_sme = '0GB';

                    if ($mtn_cg >= 1024) {
                        $calculate_mtn_cg = number_format($mtn_cg / 1024, 3) . 'TB';
                    } else {
                        $calculate_mtn_cg  =  number_format($mtn_cg, 3) . 'GB';
                    }
                    if ($mtn_g >= 1024) {
                        $calculate_mtn_g = number_format($mtn_g / 1024, 3) . 'TB';
                    } else {
                        $calculate_mtn_g  =  number_format($mtn_g, 3) . 'GB';
                    }
                    if ($mtn_sme >= 1024) {
                        $calculate_mtn_sme = number_format($mtn_sme / 1024, 3) . 'TB';
                    } else {
                        $calculate_mtn_sme =  number_format($mtn_sme, 3) . 'GB';
                    }

                    if ($glo_cg >= 1024) {
                        $calculate_glo_cg = number_format($glo_cg / 1024, 3) . 'TB';
                    } else {
                        $calculate_glo_cg  =  number_format($glo_cg, 3) . 'GB';
                    }
                    if ($glo_g >= 1024) {
                        $calculate_glo_g = number_format($glo_g / 1024, 3) . 'TB';
                    } else {
                        $calculate_glo_g  =  number_format($glo_g, 3) . 'GB';
                    }
                    if ($glo_sme >= 1024) {
                        $calculate_glo_sme = number_format($glo_sme / 1024, 3) . 'TB';
                    } else {
                        $calculate_glo_sme =  number_format($glo_sme, 3) . 'GB';
                    }


                    if ($airtel_cg >= 1024) {
                        $calculate_airtel_cg = number_format($airtel_cg / 1024, 3) . 'TB';
                    } else {
                        $calculate_airtel_cg  =  number_format($airtel_cg, 3) . 'GB';
                    }
                    if ($airtel_g >= 1024) {
                        $calculate_airtel_g = number_format($airtel_g / 1024, 3) . 'TB';
                    } else {
                        $calculate_airtel_g  =  number_format($airtel_g, 3) . 'GB';
                    }
                    if ($airtel_sme >= 1024) {
                        $calculate_airtel_sme = number_format($airtel_sme / 1024, 3) . 'TB';
                    } else {
                        $calculate_airtel_sme =  number_format($airtel_sme, 3) . 'GB';
                    }

                    if ($mobile_cg >= 1024) {
                        $calculate_mobile_cg = number_format($mobile_cg / 1024, 3) . 'TB';
                    } else {
                        $calculate_mobile_cg  =  number_format($mobile_cg, 3) . 'GB';
                    }
                    if ($mobile_g >= 1024) {
                        $calculate_mobile_g = number_format($mobile_g / 1024, 3) . 'TB';
                    } else {
                        $calculate_mobile_g  =  number_format($mobile_g, 3) . 'GB';
                    }
                    if ($mobile_sme >= 1024) {
                        $calculate_mobile_sme = number_format($mobile_sme / 1024, 3) . 'TB';
                    } else {
                        $calculate_mobile_sme =  number_format($mobile_sme, 3) . 'GB';
                    }

                    return response()->json([
                        'status' => 'success',
                        // data
                        'mtn_cg' =>  $calculate_mtn_cg,
                        'mtn_cg_bal' => number_format($mtn_cg_bal, 2),
                        'mtn_sme_bal' => number_format($mtn_sme_bal, 2),
                        'mtn_g_bal' => number_format($mtn_g_bal, 2),
                        'mtn_sme' =>  $calculate_mtn_sme,
                        'mtn_g' =>  $calculate_mtn_g,

                        'airtel_cg' =>  $calculate_airtel_cg,
                        'airtel_cg_bal' => number_format($airtel_cg_bal, 2),
                        'airtel_sme_bal' => number_format($airtel_sme_bal, 2),
                        'airtel_g_bal' => number_format($airtel_g_bal, 2),
                        'airtel_sme' =>  $calculate_airtel_sme,
                        'airtel_g' =>  $calculate_airtel_g,

                        'glo_cg' =>  $calculate_glo_cg,
                        'glo_cg_bal' => number_format($glo_cg_bal, 2),
                        'glo_sme_bal' => number_format($glo_sme_bal, 2),
                        'glo_g_bal' => number_format($glo_g_bal, 2),
                        'glo_sme' =>  $calculate_glo_sme,
                        'glo_g' =>  $calculate_glo_g,

                        'mobile_cg' =>  $calculate_mobile_cg,
                        'mobile_cg_bal' => number_format($mobile_cg_bal, 2),
                        'mobile_sme_bal' => number_format($mobile_sme_bal, 2),
                        'mobile_g_bal' => number_format($mobile_g_bal, 2),
                        'mobile_sme' =>  $calculate_mobile_sme,
                        'mobile_g' =>  $calculate_mobile_g,
                        // airtime
                        'mtn_vtu' => number_format($mtn_vtu, 2),
                        'mtn_vtu_d' => number_format($mtn_vtu_d, 2),
                        'mtn_sns' => number_format($mtn_sns, 2),
                        'mtn_sns_d' => number_format($mtn_sns_d, 2),

                        'airtel_vtu' => number_format($airtel_vtu, 2),
                        'airtel_vtu_d' => number_format($airtel_vtu_d, 2),
                        'airtel_sns' => number_format($airtel_sns, 2),
                        'airtel_sns_d' => number_format($airtel_sns_d, 2),

                        'glo_vtu' => number_format($glo_vtu, 2),
                        'glo_vtu_d' => number_format($glo_vtu_d, 2),
                        'glo_sns' => number_format($glo_sns, 2),
                        'glo_sns_d' => number_format($glo_sns_d, 2),

                        'mobile_vtu' => number_format($mobile_vtu, 2),
                        'mobile_vtu_d' => number_format($mobile_vtu_d, 2),
                        'mobile_sns' => number_format($mobile_sns, 2),
                        'mobile_sns_d' => number_format($mobile_sns_d, 2),

                        // cable
                        'dstv' => number_format($dstv, 2),
                        'dstv_c' => number_format($dstv_c, 2),
                        'gotv' => number_format($gotv, 2),
                        'gotv_c' => number_format($gotv_c, 2),
                        'startime' => number_format($startime, 2),
                        'startime_c' => number_format($startime_c, 2),

                        // exam
                        'waec' => number_format($waec, 2),
                        'waec_q' => number_format($waec_q),
                        'neco' => number_format($neco, 2),
                        'neco_q' => number_format($neco_q),
                        'nabteb' => number_format($nabteb, 2),
                        'nabteb_q' => number_format($nabteb_q),

                        // bulksms
                        'bulksms' => number_format($bulksms, 2),

                        // bill
                        'bill' => number_format($bill, 2),

                        // airtime 2 cash
                        'cash_amount' => number_format($cash, 2),
                        'cash_pay' => number_format($cash_pay, 2),

                        // deposit
                        'deposit_amount' => number_format($deposit_amount, 2),
                        'deposit_charges' => number_format($deposit_charges, 2),
                        'deposit_trans' => number_format($adex_in_trans, 1),
                        'spend_trans' => number_format($adex_out_trans, 1),
                        'spend_amount' => number_format($money_spent, 2)
                    ]);
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

    public function User(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)]);
                if ($check_user->count() == 1) {
                    $adex_username = $check_user->first();
                    $real_username = $adex_username->username;
                    // all here
                    if ($request->status == 'TODAY') {
                        $data_trans = DB::table('data')->whereDate('plan_date',  Carbon::now("Africa/Lagos"))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $airtime_trans = DB::table('airtime')->whereDate('plan_date',  Carbon::now("Africa/Lagos"))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $cable_trans = DB::table('cable')->whereDate('plan_date',  Carbon::now("Africa/Lagos"))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $exam_trans = DB::table('exam')->whereDate('plan_date',  Carbon::now("Africa/Lagos"))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $bulksms_trans = DB::table('bulksms')->whereDate('plan_date',  Carbon::now("Africa/Lagos"))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $deposit_trans = DB::table('deposit')->whereDate('date',  Carbon::now("Africa/Lagos"))->where(['status' => 1, 'username' => $real_username])->get();
                        $spend_trans = DB::table('message')->where(function ($query) {
                            $query->where('role', '!=', 'credit');
                            $query->where('plan_status', '!=', 2);
                        })->whereDate('adex_date',  Carbon::now("Africa/Lagos"))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $cash_trans = DB::table('cash')->whereDate('plan_date',  Carbon::now("Africa/Lagos"))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $bill_trans = DB::table('bill')->whereDate('plan_date',  Carbon::now("Africa/Lagos"))->where(['plan_status' => 1, 'username' => $real_username])->get();
                    } else if ($request->status == '7DAYS') {
                        $data_trans = DB::table('data')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(7))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $airtime_trans = DB::table('airtime')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(7))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $cable_trans = DB::table('cable')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(7))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $exam_trans = DB::table('exam')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(7))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $bulksms_trans = DB::table('bulksms')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(7))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $deposit_trans = DB::table('deposit')->whereDate('date', '>',  Carbon::now("Africa/Lagos")->subDays(7))->where(['status' => 1, 'username' => $real_username])->get();
                        $spend_trans = DB::table('message')->where(function ($query) {
                            $query->where('role', '!=', 'credit');
                            $query->where('plan_status', '!=', 2);
                        })->whereDate('adex_date', '>',  Carbon::now("Africa/Lagos")->subDays(7))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $cash_trans = DB::table('cash')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(7))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $bill_trans = DB::table('bill')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(7))->where(['plan_status' => 1, 'username' => $real_username])->get();
                    } else if ($request->status == '30DAYS') {
                        $data_trans = DB::table('data')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(30))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $airtime_trans = DB::table('airtime')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(30))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $cable_trans = DB::table('cable')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(30))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $exam_trans = DB::table('exam')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(30))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $bulksms_trans = DB::table('bulksms')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(30))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $deposit_trans = DB::table('deposit')->whereDate('date', '>',  Carbon::now("Africa/Lagos")->subDays(30))->where(['status' => 1, 'username' => $real_username])->get();
                        $spend_trans = DB::table('message')->where(function ($query) {
                            $query->where('role', '!=', 'credit');
                            $query->where('plan_status', '!=', 2);
                        })->whereDate('adex_date', '>',  Carbon::now("Africa/Lagos")->subDays(30))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $cash_trans = DB::table('cash')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(30))->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $bill_trans = DB::table('bill')->whereDate('plan_date', '>',  Carbon::now("Africa/Lagos")->subDays(30))->where(['plan_status' => 1, 'username' => $real_username])->get();
                    } else if ($request->status == 'ALL TIME') {
                        $data_trans = DB::table('data')->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $airtime_trans = DB::table('airtime')->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $cable_trans = DB::table('cable')->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $exam_trans = DB::table('exam')->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $bulksms_trans = DB::table('bulksms')->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $deposit_trans = DB::table('deposit')->where(['status' => 1, 'username' => $real_username])->get();
                        $spend_trans = DB::table('message')->where(function ($query) {
                            $query->where('role', '!=', 'credit');
                            $query->where('plan_status', '!=', 2);
                        })->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $cash_trans = DB::table('cash')->where(['plan_status' => 1, 'username' => $real_username])->get();
                        $bill_trans = DB::table('bill')->where(['plan_status' => 1, 'username' => $real_username])->get();
                    } else {
                        if ((isset($request->from)) and isset($request->to)) {
                            if ((!empty($request->from)) and !empty($request->to)) {
                                $start_date = Carbon::parse($request->from . ' 00:00:00')->toDateTimeString();
                                $end_date = Carbon::parse($request->to . ' 23:59:59')->toDateTimeString();
                                $data_trans = DB::table('data')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1, 'username' => $real_username])->get();
                                $airtime_trans = DB::table('airtime')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1, 'username' => $real_username])->get();
                                $cable_trans = DB::table('cable')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1, 'username' => $real_username])->get();
                                $exam_trans = DB::table('exam')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1, 'username' => $real_username])->get();
                                $bulksms_trans = DB::table('bulksms')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1, 'username' => $real_username])->get();
                                $deposit_trans = DB::table('deposit')->whereBetween('date', [$start_date, $end_date])->where(['status' => 1, 'username' => $real_username])->get();
                                $spend_trans = DB::table('message')->where(function ($query) {
                                    $query->where('role', '!=', 'credit');
                                    $query->where('plan_status', '!=', 2);
                                })->whereBetween('adex_date', [$start_date, $end_date])->where(['plan_status' => 1, 'username' => $real_username])->get();
                                $cash_trans = DB::table('cash')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1, 'username' => $real_username])->get();
                                $bill_trans = DB::table('bill')->whereBetween('plan_date', [$start_date, $end_date])->where(['plan_status' => 1, 'username' => $real_username])->get();
                            } else {
                                return response()->json([
                                    'message' => 'start date and end date required'
                                ])->setStatusCode(403);
                            }
                        } else {
                            return response()->json([
                                'message' => 'start date and end date required'
                            ])->setStatusCode(403);
                        }
                    }
                    // FOR DATA
                    $mtn_g = 0;
                    $mtn_sme = 0;
                    $mtn_cg = 0;
                    $mtn_g_bal = 0;
                    $mtn_cg_bal = 0;
                    $mtn_sme_bal = 0;

                    $airtel_g = 0;
                    $airtel_sme = 0;
                    $airtel_cg = 0;
                    $airtel_g_bal = 0;
                    $airtel_cg_bal = 0;
                    $airtel_sme_bal = 0;

                    $glo_g = 0;
                    $glo_sme = 0;
                    $glo_cg = 0;
                    $glo_g_bal = 0;
                    $glo_cg_bal = 0;
                    $glo_sme_bal = 0;

                    $mobile_g = 0;
                    $mobile_sme = 0;
                    $mobile_cg = 0;
                    $mobile_g_bal = 0;
                    $mobile_cg_bal = 0;
                    $mobile_sme_bal = 0;
                    foreach ($data_trans as $data) {
                        if ($data->network == 'MTN' and $data->network_type == 'GIFTING') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $mtn_g += $gb;
                            $mtn_g_bal  += $data->amount;
                        } else if ($data->network == 'MTN' and $data->network_type == 'SME') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $mtn_sme += $gb;
                            $mtn_sme_bal  += $data->amount;
                        } else if ($data->network == 'MTN' and $data->network_type == 'COOPERATE GIFTING') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $mtn_cg += $gb;
                            $mtn_cg_bal  += $data->amount;
                        } else if ($data->network == 'AIRTEL' and $data->network_type == 'GIFTING') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $airtel_g += $gb;
                            $airtel_g_bal  += $data->amount;
                        } else if ($data->network == 'AIRTEL' and $data->network_type == 'SME') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $airtel_sme += $gb;
                            $airtel_sme_bal  += $data->amount;
                        } else if ($data->network == 'AIRTEL' and $data->network_type == 'COOPERATE GIFTING') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $airtel_cg += $gb;
                            $airtel_cg_bal  += $data->amount;
                        } else if ($data->network == 'GLO' and $data->network_type == 'GIFTING') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $glo_g += $gb;
                            $glo_g_bal  += $data->amount;
                        } else if ($data->network == 'GLO' and $data->network_type == 'SME') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $glo_sme += $gb;
                            $glo_sme_bal  += $data->amount;
                        } else if ($data->network == 'GLO' and $data->network_type == 'COOPERATE GIFTING') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $glo_cg += $gb;
                            $glo_cg_bal  += $data->amount;
                        } else if ($data->network == '9MOBILE' and $data->network_type == 'GIFTING') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $mobile_g += $gb;
                            $mobile_g_bal  += $data->amount;
                        } else if ($data->network == '9MOBILE' and $data->network_type == 'SME') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $mobile_sme += $gb;
                            $mobile_sme_bal  += $data->amount;
                        } else if ($data->network == '9MOBILE' and $data->network_type == 'COOPERATE GIFTING') {
                            $plans = $data->plan_name;
                            $check_gb = substr($plans, -2);
                            if ($check_gb  == 'MB') {
                                $mb = rtrim($plans, "MB");
                                $gb = $mb / 1024;
                            } elseif ($check_gb == 'GB') {
                                $gb = rtrim($plans, "GB");
                            } elseif ($check_gb == 'TB') {
                                $tb = rtrim($plans, 'TB');
                                $gb = ceil($tb * 1024);
                            }
                            $mobile_cg += $gb;
                            $mobile_cg_bal  += $data->amount;
                        }
                    }

                    // airtime
                    $mtn_vtu = 0;
                    $mtn_vtu_d = 0;
                    $mtn_sns = 0;
                    $mtn_sns_d = 0;

                    $airtel_vtu = 0;
                    $airtel_vtu_d = 0;
                    $airtel_sns = 0;
                    $airtel_sns_d = 0;

                    $glo_vtu = 0;
                    $glo_vtu_d = 0;
                    $glo_sns = 0;
                    $glo_sns_d = 0;

                    $mobile_vtu = 0;
                    $mobile_vtu_d = 0;
                    $mobile_sns = 0;
                    $mobile_sns_d = 0;
                    foreach ($airtime_trans as $airtime) {
                        if ($airtime->network == 'MTN' and $airtime->network_type == 'VTU') {
                            $mtn_vtu += $airtime->amount;
                            $mtn_vtu_d += $airtime->discount;
                        }
                        if ($airtime->network == 'MTN' and $airtime->network_type == 'SNS') {
                            $mtn_sns += $airtime->amount;
                            $mtn_sns_d += $airtime->discount;
                        }

                        if ($airtime->network == 'AIRTEL' and $airtime->network_type == 'VTU') {
                            $airtel_vtu += $airtime->amount;
                            $airtel_vtu_d += $airtime->discount;
                        }
                        if ($airtime->network == 'AIRTEL' and $airtime->network_type == 'SNS') {
                            $airtel_sns += $airtime->amount;
                            $airtel_sns_d += $airtime->discount;
                        }

                        if ($airtime->network == 'GLO' and $airtime->network_type == 'VTU') {
                            $glo_vtu += $airtime->amount;
                            $glo_vtu_d += $airtime->discount;
                        }
                        if ($airtime->network == 'GLO' and $airtime->network_type == 'SNS') {
                            $glo_sns += $airtime->amount;
                            $glo_sns_d += $airtime->discount;
                        }

                        if ($airtime->network == '9MOBILE' and $airtime->network_type == 'VTU') {
                            $mobile_vtu += $airtime->amount;
                            $mobile_vtu_d += $airtime->discount;
                        }
                        if ($airtime->network == '9MOBILE' and $airtime->network_type == 'SNS') {
                            $mobile_sns += $airtime->amount;
                            $mobile_sns_d += $airtime->discount;
                        }
                    }
                    // cable
                    $dstv = 0;
                    $dstv_c = 0;
                    $gotv = 0;
                    $gotv_c = 0;
                    $startime = 0;
                    $startime_c = 0;
                    foreach ($cable_trans as $cable) {
                        if ($cable->cable_name == 'DSTV') {
                            $dstv += $cable->amount;
                            $dstv_c += $cable->charges;
                        }
                        if ($cable->cable_name == 'GOTV') {
                            $gotv += $cable->amount;
                            $gotv_c += $cable->charges;
                        }
                        if ($cable->cable_name == 'STARTIME') {
                            $startime += $cable->amount;
                            $startime_c += $cable->charges;
                        }
                    }
                    // exam
                    $waec = 0;
                    $waec_q = 0;
                    $neco = 0;
                    $neco_q = 0;
                    $nabteb = 0;
                    $nabteb_q = 0;
                    foreach ($exam_trans as $exam) {
                        if ($exam->exam_name == 'WAEC') {
                            $waec += $exam->amount;
                            $waec_q += $exam->quantity;
                        }
                        if ($exam->exam_name == 'NECO') {
                            $neco += $exam->amount;
                            $neco_q += $exam->quantity;
                        }
                        if ($exam->exam_name == 'NABTEB') {
                            $nabteb += $exam->amount;
                            $nabteb_q += $exam->quantity;
                        }
                    }
                    // bulksms
                    $bulksms = 0;
                    foreach ($bulksms_trans as $bulk) {
                        $bulksms += $bulk->amount;
                    }
                    // bill
                    $bill = 0;
                    foreach ($bill_trans as $d) {
                        $bill += $d->amount;
                    }
                    // airtime 2 cash
                    $cash = 0;
                    $cash_pay = 0;
                    foreach ($cash_trans as $d) {
                        $cash += $d->amount;
                        $cash_pay += $d->amount_credit;
                    }
                    // deposit
                    $deposit_amount = 0;
                    $deposit_charges = 0;
                    foreach ($deposit_trans as $deposit) {
                        $deposit_amount += $deposit->amount;
                        $deposit_charges += $deposit->charges;
                    }
                    $money_spent = 0;
                    foreach ($spend_trans as $spend) {
                        $money_spent += $spend->amount;
                    }
                    $adex_in = $deposit_amount;
                    $adex_out = $money_spent;
                    $total_m = $adex_in + $adex_out;
                    if ($total_m != 0) {
                        $adex_in_trans = ($adex_in / $total_m) * 100;
                        $adex_out_trans = ($adex_out / $total_m) * 100;
                    } else {
                        $adex_in_trans = 0;
                        $adex_out_trans = 0;
                    }

                    $calculate_mtn_cg = '0GB';
                    $calculate_mtn_g = '0GB';
                    $calculate_mtn_sme = '0GB';

                    $calculate_airtel_cg = '0GB';
                    $calculate_airtel_g = '0GB';
                    $calculate_airtel_sme = '0GB';

                    $calculate_glo_cg = '0GB';
                    $calculate_glo_g = '0GB';
                    $calculate_glo_sme = '0GB';

                    $calculate_mobile_cg = '0GB';
                    $calculate_mobile_g = '0GB';
                    $calculate_mobile_sme = '0GB';

                    if ($mtn_cg >= 1024) {
                        $calculate_mtn_cg = number_format($mtn_cg / 1024, 3) . 'TB';
                    } else {
                        $calculate_mtn_cg  =  number_format($mtn_cg, 3) . 'GB';
                    }
                    if ($mtn_g >= 1024) {
                        $calculate_mtn_g = number_format($mtn_g / 1024, 3) . 'TB';
                    } else {
                        $calculate_mtn_g  =  number_format($mtn_g, 3) . 'GB';
                    }
                    if ($mtn_sme >= 1024) {
                        $calculate_mtn_sme = number_format($mtn_sme / 1024, 3) . 'TB';
                    } else {
                        $calculate_mtn_sme =  number_format($mtn_sme, 3) . 'GB';
                    }

                    if ($glo_cg >= 1024) {
                        $calculate_glo_cg = number_format($glo_cg / 1024, 3) . 'TB';
                    } else {
                        $calculate_glo_cg  =  number_format($glo_cg, 3) . 'GB';
                    }
                    if ($glo_g >= 1024) {
                        $calculate_glo_g = number_format($glo_g / 1024, 3) . 'TB';
                    } else {
                        $calculate_glo_g  =  number_format($glo_g, 3) . 'GB';
                    }
                    if ($glo_sme >= 1024) {
                        $calculate_glo_sme = number_format($glo_sme / 1024, 3) . 'TB';
                    } else {
                        $calculate_glo_sme =  number_format($glo_sme, 3) . 'GB';
                    }


                    if ($airtel_cg >= 1024) {
                        $calculate_airtel_cg = number_format($airtel_cg / 1024, 3) . 'TB';
                    } else {
                        $calculate_airtel_cg  =  number_format($airtel_cg, 3) . 'GB';
                    }
                    if ($airtel_g >= 1024) {
                        $calculate_airtel_g = number_format($airtel_g / 1024, 3) . 'TB';
                    } else {
                        $calculate_airtel_g  =  number_format($airtel_g, 3) . 'GB';
                    }
                    if ($airtel_sme >= 1024) {
                        $calculate_airtel_sme = number_format($airtel_sme / 1024, 3) . 'TB';
                    } else {
                        $calculate_airtel_sme =  number_format($airtel_sme, 3) . 'GB';
                    }

                    if ($mobile_cg >= 1024) {
                        $calculate_mobile_cg = number_format($mobile_cg / 1024, 3) . 'TB';
                    } else {
                        $calculate_mobile_cg  =  number_format($mobile_cg, 3) . 'GB';
                    }
                    if ($mobile_g >= 1024) {
                        $calculate_mobile_g = number_format($mobile_g / 1024, 3) . 'TB';
                    } else {
                        $calculate_mobile_g  =  number_format($mobile_g, 3) . 'GB';
                    }
                    if ($mobile_sme >= 1024) {
                        $calculate_mobile_sme = number_format($mobile_sme / 1024, 3) . 'TB';
                    } else {
                        $calculate_mobile_sme =  number_format($mobile_sme, 3) . 'GB';
                    }


                    return response()->json([
                        'status' => 'success',
                        // data
                        'mtn_cg' =>  $calculate_mtn_cg,
                        'mtn_cg_bal' => number_format($mtn_cg_bal, 2),
                        'mtn_sme_bal' => number_format($mtn_sme_bal, 2),
                        'mtn_g_bal' => number_format($mtn_g_bal, 2),
                        'mtn_sme' =>  $calculate_mtn_sme,
                        'mtn_g' =>  $calculate_mtn_g,

                        'airtel_cg' =>  $calculate_airtel_cg,
                        'airtel_cg_bal' => number_format($airtel_cg_bal, 2),
                        'airtel_sme_bal' => number_format($airtel_sme_bal, 2),
                        'airtel_g_bal' => number_format($airtel_g_bal, 2),
                        'airtel_sme' =>  $calculate_airtel_sme,
                        'airtel_g' =>  $calculate_airtel_g,

                        'glo_cg' =>  $calculate_glo_cg,
                        'glo_cg_bal' => number_format($glo_cg_bal, 2),
                        'glo_sme_bal' => number_format($glo_sme_bal, 2),
                        'glo_g_bal' => number_format($glo_g_bal, 2),
                        'glo_sme' =>  $calculate_glo_sme,
                        'glo_g' =>  $calculate_glo_g,

                        'mobile_cg' =>  $calculate_mobile_cg,
                        'mobile_cg_bal' => number_format($mobile_cg_bal, 2),
                        'mobile_sme_bal' => number_format($mobile_sme_bal, 2),
                        'mobile_g_bal' => number_format($mobile_g_bal, 2),
                        'mobile_sme' =>  $calculate_mobile_sme,
                        'mobile_g' =>  $calculate_mobile_g,
                        // airtime
                        'mtn_vtu' => number_format($mtn_vtu, 2),
                        'mtn_vtu_d' => number_format($mtn_vtu_d, 2),
                        'mtn_sns' => number_format($mtn_sns, 2),
                        'mtn_sns_d' => number_format($mtn_sns_d, 2),

                        'airtel_vtu' => number_format($airtel_vtu, 2),
                        'airtel_vtu_d' => number_format($airtel_vtu_d, 2),
                        'airtel_sns' => number_format($airtel_sns, 2),
                        'airtel_sns_d' => number_format($airtel_sns_d, 2),

                        'glo_vtu' => number_format($glo_vtu, 2),
                        'glo_vtu_d' => number_format($glo_vtu_d, 2),
                        'glo_sns' => number_format($glo_sns, 2),
                        'glo_sns_d' => number_format($glo_sns_d, 2),

                        'mobile_vtu' => number_format($mobile_vtu, 2),
                        'mobile_vtu_d' => number_format($mobile_vtu_d, 2),
                        'mobile_sns' => number_format($mobile_sns, 2),
                        'mobile_sns_d' => number_format($mobile_sns_d, 2),

                        // cable
                        'dstv' => number_format($dstv, 2),
                        'dstv_c' => number_format($dstv_c, 2),
                        'gotv' => number_format($gotv, 2),
                        'gotv_c' => number_format($gotv_c, 2),
                        'startime' => number_format($startime, 2),
                        'startime_c' => number_format($startime_c, 2),

                        // exam
                        'waec' => number_format($waec, 2),
                        'waec_q' => number_format($waec_q),
                        'neco' => number_format($neco, 2),
                        'neco_q' => number_format($neco_q),
                        'nabteb' => number_format($nabteb, 2),
                        'nabteb_q' => number_format($nabteb_q),

                        // bulksms
                        'bulksms' => number_format($bulksms, 2),

                        // bill
                        'bill' => number_format($bill, 2),

                        // airtime 2 cash
                        'cash_amount' => number_format($cash, 2),
                        'cash_pay' => number_format($cash_pay, 2),

                        // deposit
                        'deposit_amount' => number_format($deposit_amount, 2),
                        'deposit_charges' => number_format($deposit_charges, 2),
                        'deposit_trans' => number_format($adex_in_trans, 1),
                        'spend_trans' => number_format($adex_out_trans, 1),
                        'spend_amount' => number_format($money_spent, 2)
                    ]);
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
