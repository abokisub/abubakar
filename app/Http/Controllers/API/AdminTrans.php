<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class  AdminTrans extends Controller
{
    public function AllTrans(Request $request)
    {

        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                });
                if ($check_user->count() > 0) {
                    $search = strtolower($request->search);
                    $database_name = strtolower($request->database_name);
                    if ($database_name === 'bank_trans') {
                        if (!empty($search)) {
                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'bank_trans' => DB::table('bank_transfer')->Where(function ($query) use ($search) {
                                        $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('date', 'LIKE', "%$search%")->orWhere('account_name', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('account_number', 'LIKE', "%$search%")->orWhere('bank_name', 'LIKE', "%$search%")->orWhere('bank_code', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'bank_trans' => DB::table('bank_transfer')->where(['plan_status' => $request->status])->Where(function ($query) use ($search) {
                                        $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('date', 'LIKE', "%$search%")->orWhere('account_name', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('account_number', 'LIKE', "%$search%")->orWhere('bank_name', 'LIKE', "%$search%")->orWhere('bank_code', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        } else {
                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'bank_trans' => DB::table('bank_transfer')->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'bank_trans' => DB::table('bank_transfer')->where(['plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        }
                    } else if ($database_name == 'cable_trans') {
                        if (!empty($search)) {
                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'cable_trans' => DB::table('cable')->Where(function ($query) use ($search) {
                                        $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('charges', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('cable_plan', 'LIKE', "%$search%")->orWhere('cable_name', 'LIKE', "%$search%")->orWhere('iuc', 'LIKE', "%$search%")->orWhere('customer_name', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'cable_trans' => DB::table('cable')->where(['plan_status' => $request->status])->Where(function ($query) use ($search) {
                                        $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('charges', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('cable_plan', 'LIKE', "%$search%")->orWhere('cable_name', 'LIKE', "%$search%")->orWhere('iuc', 'LIKE', "%$search%")->orWhere('customer_name', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        } else {
                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'cable_trans' => DB::table('cable')->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'cable_trans' => DB::table('cable')->where(['plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        }
                    } elseif ($database_name == 'bill_trans') {
                        if (!empty($search)) {
                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'bill_trans' => DB::table('bill')->Where(function ($query) use ($search) {
                                        $query->orWhere('disco_name', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('meter_number', 'LIKE', "%$search%")->orWhere('meter_type', 'LIKE', "%$search%")->orWhere('customer_name', 'LIKE', "%$search%")->orWhere('token', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'bill_trans' => DB::table('bill')->where(['plan_status' => $request->status])->Where(function ($query) use ($search) {
                                        $query->orWhere('disco_name', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('meter_number', 'LIKE', "%$search%")->orWhere('meter_type', 'LIKE', "%$search%")->orWhere('customer_name', 'LIKE', "%$search%")->orWhere('token', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        } else {

                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'bill_trans' => DB::table('bill')->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'bill_trans' => DB::table('bill')->where(['plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        }
                    } else if ($database_name == 'bulksms_trans') {
                        if (!empty($search)) {
                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'bulksms_trans' => DB::table('bulksms')->Where(function ($query) use ($search) {
                                        $query->orWhere('correct_number', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('wrong_number', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('total_correct_number', 'LIKE', "%$search%")->orWhere('total_wrong_number', 'LIKE', "%$search%")->orWhere('message', 'LIKE', "%$search%")->orWhere('sender_name', 'LIKE', "%$search%")->orWhere('numbers', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'bulksms_trans' => DB::table('bulksms')->where(['plan_status' => $request->status])->Where(function ($query) use ($search) {
                                        $query->orWhere('correct_number', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('wrong_number', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('total_correct_number', 'LIKE', "%$search%")->orWhere('total_wrong_number', 'LIKE', "%$search%")->orWhere('message', 'LIKE', "%$search%")->orWhere('sender_name', 'LIKE', "%$search%")->orWhere('numbers', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        } else {
                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'bulksms_trans' => DB::table('bulksms')->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'bulksms_trans' => DB::table('bulksms')->where(['plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        }
                    } else if ($database_name == 'cash_trans') {
                        if (!empty($search)) {
                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'cash_trans' => DB::table('cash')->Where(function ($query) use ($search) {
                                        $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('amount_credit', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('payment_type', 'LIKE', "%$search%")->orWhere('network', 'LIKE', "%$search%")->orWhere('sender_number', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'cash_trans' => DB::table('cash')->where(['plan_status' => $request->status])->Where(function ($query) use ($search) {
                                        $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('amount_credit', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('payment_type', 'LIKE', "%$search%")->orWhere('network', 'LIKE', "%$search%")->orWhere('sender_number', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        } else {
                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'cash_trans' => DB::table('cash')->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'cash_trans' => DB::table('cash')->where(['plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        }
                    } else if ($database_name == 'result_trans') {
                        if (!empty($search)) {
                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'result_trans' => DB::table('exam')->Where(function ($query) use ($search) {
                                        $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('purchase_code', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('exam_name', 'LIKE', "%$search%")->orWhere('quantity', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'result_trans' => DB::table('exam')->where(['plan_status' => $request->status])->Where(function ($query) use ($search) {
                                        $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('purchase_code', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('exam_name', 'LIKE', "%$search%")->orWhere('quantity', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        } else {
                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'result_trans' => DB::table('exam')->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'result_trans' => DB::table('exam')->where(['plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        }
                    } else {
                        return response()->json([


                            'message' => 'Not invalid'

                        ])->setStatusCode(403);
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
    public function DepositTransSum(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                });
                if ($check_user->count() > 0) {
                    $search = strtolower($request->search);
                    if (!empty($search)) {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'deposit_trans' => DB::table('deposit')->Where(function ($query) use ($search) {
                                    $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('wallet_type', 'LIKE', "%$search%")->orWhere('type', 'LIKE', "%$search%")->orWhere('credit_by', 'LIKE', "%$search%")->orWhere('charges', 'LIKE', "%$search%")->orWhere('monify_ref', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex),
                            ]);
                        } else {
                            return response()->json([
                                'deposit_trans' => DB::table('deposit')->where(['status' => $request->status])->Where(function ($query) use ($search) {
                                    $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('wallet_type', 'LIKE', "%$search%")->orWhere('type', 'LIKE', "%$search%")->orWhere('credit_by', 'LIKE', "%$search%")->orWhere('charges', 'LIKE', "%$search%")->orWhere('monify_ref', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        }
                    } else {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'deposit_trans' => DB::table('deposit')->orderBy('id', 'desc')->paginate($request->adex),
                            ]);
                        } else {
                            return response()->json([
                                'deposit_trans' => DB::table('deposit')->where(['status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
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
            return redirect(env('ERROR_500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function StockTransSum(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                });
                if ($check_user->count() > 0) {
                    $search = strtolower($request->search);

                    if (!empty($search)) {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'data_trans' => DB::table('data')->where('wallet', '!=', 'wallet')->Where(function ($query) use ($search) {
                                    $query->orWhere('network', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('api_response', 'LIKE', "%$search%")->orWhere('plan_phone', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('network_type', 'LIKE', "%$search%")->orWhere('wallet', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'data_trans' => DB::table('data')->where(['plan_status' => $request->status])->where('wallet', '!=', 'wallet')->Where(function ($query) use ($search) {
                                    $query->orWhere('network', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('api_response', 'LIKE', "%$search%")->orWhere('plan_phone', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('network_type', 'LIKE', "%$search%")->orWhere('wallet', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        }
                    } else {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'data_trans' => DB::table('data')->where('wallet', '!=', 'wallet')->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'data_trans' => DB::table('data')->where('wallet', '!=', 'wallet')->where(['plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
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
            return redirect(env('ERROR_500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function AirtimeTransSum(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                });
                if ($check_user->count() > 0) {
                    $search = strtolower($request->search);
                    if (!empty($search)) {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'airtime_trans' => DB::table('airtime')->Where(function ($query) use ($search) {
                                    $query->orWhere('network', 'LIKE', "%$search%")->orWhere('network_type', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('discount', 'LIKE', "%$search%")->orWhere('plan_phone', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'airtime_trans' => DB::table('airtime')->where(['plan_status' => $request->status])->Where(function ($query) use ($search) {
                                    $query->orWhere('network', 'LIKE', "%$search%")->orWhere('network_type', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('discount', 'LIKE', "%$search%")->orWhere('plan_phone', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        }
                    } else {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'airtime_trans' => DB::table('airtime')->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'airtime_trans' => DB::table('airtime')->where(['plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
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
            return redirect(env('ERROR_500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function DataTransSum(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                });
                if ($check_user->count() > 0) {

                    $search = strtolower($request->search);

                    if (!empty($search)) {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'data_trans' => DB::table('data')->Where(function ($query) use ($search) {
                                    $query->orWhere('network', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('api_response', 'LIKE', "%$search%")->orWhere('plan_phone', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('network_type', 'LIKE', "%$search%")->orWhere('wallet', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'data_trans' => DB::table('data')->where(['plan_status' => $request->status])->Where(function ($query) use ($search) {
                                    $query->orWhere('network', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('api_response', 'LIKE', "%$search%")->orWhere('plan_phone', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('network_type', 'LIKE', "%$search%")->orWhere('wallet', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        }
                    } else {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'data_trans' => DB::table('data')->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'data_trans' => DB::table('data')->where(['plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
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
            return redirect(env('ERROR_500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function AllSummaryTrans(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                });
                if ($check_user->count() > 0) {
                    $search = strtolower($request->search);
                    if (!empty($search)) {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'all_summary' => DB::table('message')->Where(function ($query) use ($search) {
                                    $query->orWhere('message', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('adex_date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'all_summary' => DB::table('message')->where(['plan_status' => $request->status])->Where(function ($query) use ($search) {
                                    $query->orWhere('message', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%")->orWhere('adex_date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        }
                    } else {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'all_summary' => DB::table('message')->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'all_summary' => DB::table('message')->where(['plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
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
            return redirect(env('ERROR_500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function DataRefund(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                });
                if ($check_user->count() > 0) {
                    if (DB::table('data')->where('transid', $request->transid)->count() == 1) {
                        $trans = DB::table('data')->where('transid', $request->transid)->first();
                        $user = DB::table('user')->where(['username' => $trans->username])->first();
                        if ($request->plan_status == 1) {
                            $api_response =  "You have successfully purchased " . $trans->network . ' ' . $trans->plan_name . ' to ' . $trans->plan_phone;
                            $status = 'success';
                            // make success
                            if ($trans->plan_status == 0) {
                                //
                                DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1, 'message' =>   "You have successfully purchased " . $trans->network . ' ' . $trans->plan_name . ' to ' . $trans->plan_phone]);
                                DB::table('data')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1]);
                            } else if ($trans->plan_status == 2) {
                                if (strtolower($trans->wallet) == 'wallet') {
                                    $b = DB::table('user')->where('username', $trans->username)->first();
                                    $user_balance = $b->bal;
                                    DB::table('user')->where('username', $trans->username)->update(['bal' => $user_balance - $trans->amount]);
                                } else {
                                    $wallet_bal = strtolower($trans->wallet) . "_bal";
                                    $b = DB::table('wallet_funding')->where('username', $trans->username)->first();
                                    $user_balance = $b->$wallet_bal;
                                    DB::table('wallet_funding')->where('username', $trans->username)->update([$wallet_bal => $user_balance - $trans->amount]);
                                }
                                DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1, 'message' =>  "You have successfully purchased " . $trans->network . ' ' . $trans->plan_name . ' to ' . $trans->plan_phone, 'oldbal' => $user_balance, 'newbal' => $user_balance - $trans->amount]);
                                DB::table('data')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1,  'oldbal' => $user_balance, 'newbal' => $user_balance - $trans->amount]);
                            } else {
                                return response()->json([
                                    'status' => 403,
                                    'message' => 'Not Stated'
                                ])->setStatusCode(403);
                            }
                        } else if ($request->plan_status == 2) {
                            // refund user
                            if (strtolower($trans->wallet) == 'wallet') {
                                $b = DB::table('user')->where('username', $trans->username)->first();
                                $user_balance = $b->bal;
                                DB::table('user')->where('username', $trans->username)->update(['bal' => $user_balance + $trans->amount]);
                            } else {
                                $wallet_bal = strtolower($trans->wallet) . "_bal";
                                $b = DB::table('wallet_funding')->where('username', $trans->username)->first();
                                $user_balance = $b->$wallet_bal;
                                DB::table('wallet_funding')->where('username', $trans->username)->update([$wallet_bal => $user_balance + $trans->amount]);
                            }
                            DB::table('data')->where(['username' => $trans->username, 'transid' => $trans->transid])->delete();
                            DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->delete();
                            $data_new = [
                                'plan_status' => 2,
                                'oldbal' => $user_balance,
                                'newbal' => $user_balance + $trans->amount,
                                'network' => $trans->network,
                                'network_type' => $trans->network_type,
                                'plan_name' => $trans->plan_name,
                                'amount' => $trans->amount,
                                'transid' => $trans->transid,
                                'plan_phone' => $trans->plan_phone,
                                'plan_date' => $this->system_date(),
                                'system' => $trans->system,
                                'wallet' => $trans->wallet,
                                'api_response' => null,
                                'username' => $trans->username
                            ];
                            $message_new = [
                                'plan_status' => 2,
                                'message' =>  "Transaction Fail (Refund)" . $trans->network . ' ' . $trans->plan_name . ' to ' . $trans->plan_phone,
                                'oldbal' => $user_balance,
                                'newbal' => $user_balance + $trans->amount,
                                'username' => $trans->username,
                                'adex_date' => $this->system_date(),
                                'transid' => $trans->transid,
                                'role' => 'data',
                                'amount' => $trans->amount
                            ];

                            DB::table('message')->insert($message_new);
                            DB::table('data')->insert($data_new);
                            $api_response = "Transaction Fail (Refund)" . $trans->network . ' ' . $trans->plan_name . ' to ' . $trans->plan_phone;
                            $status = 'fail';
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Not Stated'
                            ])->setStatusCode(403);
                        }
                        if ($status) {
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $user->webhook);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['status' => $status, 'request-id' => $trans->transid, 'response' => $api_response]));  //Post Fields
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_exec($ch);
                            curl_close($ch);
                        }
                        // send message here
                        return response()->json([
                            'status' => 'success',
                        ]);
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Invalid Transaction id'
                        ])->setStatusCode(403);
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
    public function AirtimeRefund(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                });
                if ($check_user->count() > 0) {
                    if (DB::table('airtime')->where('transid', $request->transid)->count() == 1) {
                        $trans = DB::table('airtime')->where('transid', $request->transid)->first();
                        if ($request->plan_status == 1) {
                            // make success
                            if ($trans->plan_status == 0) {
                                //
                                DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1, 'message' =>   "You have successfully purchased " . $trans->network . ' ' . $trans->network_type . ' to ' . $trans->plan_phone]);
                                DB::table('airtime')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1]);
                            } else if ($trans->plan_status == 2) {

                                $b = DB::table('user')->where('username', $trans->username)->first();
                                $user_balance = $b->bal;
                                DB::table('user')->where('username', $trans->username)->update(['bal' => $user_balance - $trans->discount]);

                                DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1, 'message' =>  "You have successfully purchased " . $trans->network . ' ' . $trans->network_type . ' to ' . $trans->plan_phone, 'oldbal' => $user_balance, 'newbal' => $user_balance - $trans->discount]);
                                DB::table('airtime')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1,  'oldbal' => $user_balance, 'newbal' => $user_balance - $trans->discount]);
                            } else {
                                return response()->json([
                                    'status' => 403,
                                    'message' => 'Not Stated'
                                ])->setStatusCode(403);
                            }
                        } else if ($request->plan_status == 2) {
                            // refund user

                            $b = DB::table('user')->where('username', $trans->username)->first();
                            $user_balance = $b->bal;
                            DB::table('user')->where('username', $trans->username)->update(['bal' => $user_balance + $trans->discount]);

                            DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 2, 'message' =>  "Transaction Fail (Refund)" . $trans->network . ' ' . $trans->network_type . ' to ' . $trans->plan_phone, 'oldbal' => $user_balance, 'newbal' => $user_balance + $trans->discount]);
                            DB::table('airtime')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 2,  'oldbal' => $user_balance, 'newbal' => $user_balance + $trans->discount]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Not Stated'
                            ])->setStatusCode(403);
                        }
                        // send message here
                        return response()->json([
                            'status' => 'success',

                        ]);
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Invalid Transaction id'
                        ])->setStatusCode(403);
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
    public function CableRefund(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                });
                if ($check_user->count() > 0) {
                    if (DB::table('cable')->where('transid', $request->transid)->count() == 1) {
                        $trans = DB::table('cable')->where('transid', $request->transid)->first();
                        $adex_amount = $trans->amount + $trans->charges;
                        if ($request->plan_status == 1) {
                            // make success
                            if ($trans->plan_status == 0) {
                                //
                                DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1, 'message' =>   "You have successfully purchased " . $trans->cable_name . ' ' . $trans->cable_plan . ' to ' . $trans->iuc]);
                                DB::table('cable')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1]);
                            } else if ($trans->plan_status == 2) {

                                $b = DB::table('user')->where('username', $trans->username)->first();
                                $user_balance = $b->bal;
                                DB::table('user')->where('username', $trans->username)->update(['bal' => $user_balance - $adex_amount]);

                                DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1, 'message' =>  "You have successfully purchased " . $trans->cable_name . ' ' . $trans->cable_plan . ' to ' . $trans->iuc, 'oldbal' => $user_balance, 'newbal' => $user_balance - $adex_amount]);
                                DB::table('cable')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1,  'oldbal' => $user_balance, 'newbal' => $user_balance - $adex_amount]);
                            } else {
                                return response()->json([
                                    'status' => 403,
                                    'message' => 'Not Stated'
                                ])->setStatusCode(403);
                            }
                        } else if ($request->plan_status == 2) {
                            // refund user

                            $b = DB::table('user')->where('username', $trans->username)->first();
                            $user_balance = $b->bal;
                            DB::table('user')->where('username', $trans->username)->update(['bal' => $user_balance + $adex_amount]);

                            DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 2, 'message' =>  "Transaction Fail (Refund)" . $trans->cable_name . ' ' . $trans->cable_plan . ' to ' . $trans->iuc, 'oldbal' => $user_balance, 'newbal' => $user_balance + $adex_amount]);
                            DB::table('cable')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 2,  'oldbal' => $user_balance, 'newbal' => $user_balance + $adex_amount]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Not Stated'
                            ])->setStatusCode(403);
                        }
                        // send message here
                        return response()->json([
                            'status' => 'success',

                        ]);
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Invalid Transaction id'
                        ])->setStatusCode(403);
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
    public function BillRefund(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                });
                if ($check_user->count() > 0) {
                    if (DB::table('bill')->where('transid', $request->transid)->count() == 1) {
                        $trans = DB::table('bill')->where('transid', $request->transid)->first();
                        $adex_amount = $trans->amount + $trans->charges;
                        if ($request->plan_status == 1) {
                            // make success
                            if ($trans->plan_status == 0) {
                                //
                                DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1, 'message' =>   "You have successfully purchased " . $trans->disco_name . ' ' . $trans->meter_type . ' to ' . $trans->meter_number]);
                                DB::table('bill')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1]);
                            } else if ($trans->plan_status == 2) {

                                $b = DB::table('user')->where('username', $trans->username)->first();
                                $user_balance = $b->bal;
                                DB::table('user')->where('username', $trans->username)->update(['bal' => $user_balance - $adex_amount]);

                                DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1, 'message' =>  "You have successfully purchased " . $trans->disco_name . ' ' . $trans->meter_type . ' to ' . $trans->meter_number, 'oldbal' => $user_balance, 'newbal' => $user_balance - $adex_amount]);
                                DB::table('bill')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1,  'oldbal' => $user_balance, 'newbal' => $user_balance - $adex_amount]);
                            } else {
                                return response()->json([
                                    'status' => 403,
                                    'message' => 'Not Stated'
                                ])->setStatusCode(403);
                            }
                        } else if ($request->plan_status == 2) {
                            // refund user

                            $b = DB::table('user')->where('username', $trans->username)->first();
                            $user_balance = $b->bal;
                            DB::table('user')->where('username', $trans->username)->update(['bal' => $user_balance + $adex_amount]);

                            DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 2, 'message' =>  "Transaction Fail (Refund) " . $trans->disco_name . ' ' . $trans->meter_type . ' to ' . $trans->meter_number, 'oldbal' => $user_balance, 'newbal' => $user_balance + $adex_amount]);
                            DB::table('bill')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 2,  'oldbal' => $user_balance, 'newbal' => $user_balance + $adex_amount]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Not Stated'
                            ])->setStatusCode(403);
                        }
                        // send message here
                        return response()->json([
                            'status' => 'success',

                        ]);
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Invalid Transaction id'
                        ])->setStatusCode(403);
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
    public function ResultRefund(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                });
                if ($check_user->count() > 0) {
                    if (DB::table('exam')->where('transid', $request->transid)->count() == 1) {
                        $trans = DB::table('exam')->where('transid', $request->transid)->first();
                        $adex_amount = $trans->amount;
                        if ($request->plan_status == 1) {
                            // make success
                            if ($trans->plan_status == 0) {
                                //
                                DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1, 'message' =>   "You have successfully purchased " . $trans->exam_name . ' E-pin']);
                                DB::table('exam')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1]);
                            } else if ($trans->plan_status == 2) {

                                $b = DB::table('user')->where('username', $trans->username)->first();
                                $user_balance = $b->bal;
                                DB::table('user')->where('username', $trans->username)->update(['bal' => $user_balance - $adex_amount]);

                                DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1, 'message' =>  "You have successfully purchased " . $trans->exam_name . ' E-pin', 'oldbal' => $user_balance, 'newbal' => $user_balance - $adex_amount]);
                                DB::table('exam')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1,  'oldbal' => $user_balance, 'newbal' => $user_balance - $adex_amount]);
                            } else {
                                return response()->json([
                                    'status' => 403,
                                    'message' => 'Not Stated'
                                ])->setStatusCode(403);
                            }
                        } else if ($request->plan_status == 2) {
                            // refund user

                            $b = DB::table('user')->where('username', $trans->username)->first();
                            $user_balance = $b->bal;
                            DB::table('user')->where('username', $trans->username)->update(['bal' => $user_balance + $adex_amount]);

                            DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 2, 'message' =>  "Transaction Fail (Refund)" . $trans->exam_name . 'E-pin ', 'oldbal' => $user_balance, 'newbal' => $user_balance + $adex_amount]);
                            DB::table('exam')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 2,  'oldbal' => $user_balance, 'newbal' => $user_balance + $adex_amount]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Not Stated'
                            ])->setStatusCode(403);
                        }
                        // send message here
                        return response()->json([
                            'status' => 'success',

                        ]);
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Invalid Transaction id'
                        ])->setStatusCode(403);
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
    public function BulkSmsRefund(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                });
                if ($check_user->count() > 0) {
                    if (DB::table('bulksms')->where('transid', $request->transid)->count() == 1) {
                        $trans = DB::table('bulksms')->where('transid', $request->transid)->first();
                        $adex_amount = $trans->amount;
                        if ($request->plan_status == 1) {
                            // make success
                            if ($trans->plan_status == 0) {
                                //
                                DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1, 'message' =>   "Bulk SMS Sent successfully"]);
                                DB::table('bulksms')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1]);
                            } else if ($trans->plan_status == 2) {

                                $b = DB::table('user')->where('username', $trans->username)->first();
                                $user_balance = $b->bal;
                                DB::table('user')->where('username', $trans->username)->update(['bal' => $user_balance - $adex_amount]);

                                DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1, 'message' =>  "Bulk SMS sent successfuly", 'oldbal' => $user_balance, 'newbal' => $user_balance - $adex_amount]);
                                DB::table('bulksms')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1,  'oldbal' => $user_balance, 'newbal' => $user_balance - $adex_amount]);
                            } else {
                                return response()->json([
                                    'status' => 403,
                                    'message' => 'Not Stated'
                                ])->setStatusCode(403);
                            }
                        } else if ($request->plan_status == 2) {
                            // refund user

                            $b = DB::table('user')->where('username', $trans->username)->first();
                            $user_balance = $b->bal;
                            DB::table('user')->where('username', $trans->username)->update(['bal' => $user_balance + $adex_amount]);

                            DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 2, 'message' =>  "Bulksms Fail (Refund)", 'oldbal' => $user_balance, 'newbal' => $user_balance + $adex_amount]);
                            DB::table('bulksms')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 2,  'oldbal' => $user_balance, 'newbal' => $user_balance + $adex_amount]);
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Not Stated'
                            ])->setStatusCode(403);
                        }
                        // send message here
                        return response()->json([
                            'status' => 'success',

                        ]);
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Invalid Transaction id'
                        ])->setStatusCode(403);
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
    public function AirtimeCashRefund(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                });
                if ($check_user->count() > 0) {
                    if (DB::table('cash')->where('transid', $request->transid)->count() == 1) {
                        $trans = DB::table('cash')->where('transid', $request->transid)->first();
                        $adex_amount = $trans->amount_credit;
                        if ($request->plan_status == 1) {
                            // make success
                            $message = [
                                'username' => $trans->username,
                                'message' => 'airtime 2 cash approved',
                                'date' => $this->system_date(),
                                'adex' => 0
                            ];
                            DB::table('notif')->insert($message);
                            if (strtolower($trans->payment_type) != 'wallet') {
                                //
                                DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1, 'message' =>   "Airtime 2 Cash Success"]);
                                DB::table('cash')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1]);
                            } else {
                                $b = DB::table('user')->where('username', $trans->username)->first();
                                $user_balance = $b->bal;
                                DB::table('user')->where('username', $trans->username)->update(['bal' => $user_balance + $adex_amount]);

                                DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1, 'message' =>  "Airtime 2 Cash Successs", 'oldbal' => $user_balance, 'newbal' => $user_balance + $adex_amount]);
                                DB::table('cash')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1,  'oldbal' => $user_balance, 'newbal' => $user_balance + $adex_amount]);
                            }
                        } else if ($request->plan_status == 2) {
                            $message = [
                                'username' => $trans->username,
                                'message' => 'airtime 2 cash declined',
                                'date' => $this->system_date(),
                                'adex' => 0
                            ];
                            DB::table('notif')->insert($message);
                            // refund user
                            if (strtolower($trans->payment_type) != 'wallet') {
                                //
                                DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 2, 'message' =>   "Airtime 2 Cash fail"]);
                                DB::table('cash')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 2]);
                            } else {
                                if ($trans->plan_status == 1) {
                                    $b = DB::table('user')->where('username', $trans->username)->first();
                                    $user_balance = $b->bal;
                                    DB::table('user')->where('username', $trans->username)->update(['bal' => $user_balance - $adex_amount]);

                                    DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 2, 'message' =>  "Airtime 2 Cash fail", 'oldbal' => $user_balance, 'newbal' => $user_balance - $adex_amount]);
                                    DB::table('cash')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 2,  'oldbal' => $user_balance, 'newbal' => $user_balance - $adex_amount]);
                                } else {
                                    //
                                    DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 2, 'message' =>   "Airtime 2 Cash fail"]);
                                    DB::table('cash')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 2]);
                                }
                            }
                        } else {
                            return response()->json([
                                'status' => 403,
                                'message' => 'Not Stated'
                            ])->setStatusCode(403);
                        }
                        // send message here
                        return response()->json([
                            'status' => 'success',

                        ]);
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Invalid Transaction id'
                        ])->setStatusCode(403);
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
    public function ManualSuccess(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                });
                if ($check_user->count() > 0) {
                    if (DB::table('bank_transfer')->where('transid', $request->transid)->count() == 1) {
                        $trans = DB::table('bank_transfer')->where('transid', $request->transid)->first();
                        if ($request->plan_status == 1) {
                            // make success
                            $message = [
                                'username' => $trans->username,
                                'message' => 'manual funding approved',
                                'date' => $this->system_date(),
                                'adex' => 0
                            ];
                            DB::table('notif')->insert($message);

                            //
                            DB::table('bank_transfer')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1]);
                        } else {
                            // make fail
                            $message = [
                                'username' => $trans->username,
                                'message' => 'manual funding decliend',
                                'date' => $this->system_date(),
                                'adex' => 0
                            ];
                            DB::table('notif')->insert($message);
                            DB::table('bank_transfer')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 2]);
                        }
                        // send message here
                        return response()->json([
                            'status' => 'success',

                        ]);
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Invalid Transaction id'
                        ])->setStatusCode(403);
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

    public function DataRechargeCard(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                });
                if ($check_user->count() > 0) {
                    $search = strtolower($request->search);
                    $database_name = strtolower($request->database_name);
                    if ($database_name == 'data_card') {
                        if (!empty($searh)) {
                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'data_card' => DB::table('data_card')->Where(function ($query) use ($search) {
                                        $query->orWhere('username', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('load_pin', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('plan_type', 'LIKE', "%$search%")->orWhere('card_name', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'data_card' => DB::table('data_card')->Where(function ($query) use ($search) {
                                        $query->orWhere('username', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('load_pin', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('plan_type', 'LIKE', "%$search%")->orWhere('card_name', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%");
                                    })->where(['plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        } else {
                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'data_card' => DB::table('data_card')->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'data_card' => DB::table('data_card')->where(['plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        }
                    } else if ($database_name == 'recharge_card') {
                        if (!empty($search)) {
                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'recharge_card' => DB::table('recharge_card')->Where(function ($query) use ($search) {
                                        $query->orWhere('username', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('load_pin', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('card_name', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'recharge_card' => DB::table('recharge_card')->Where(function ($query) use ($search) {
                                        $query->orWhere('username', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('load_pin', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('card_name', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%");
                                    })->where(['plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        } else {
                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'recharge_card' => DB::table('recharge_card')->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'recharge_card' => DB::table('recharge_card')->where(['plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        }
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Not Found'
                        ])->setStatusCode(403);
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

    public function DataCardRefund(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                });
                if ($check_user->count() > 0) {
                    $data_card_d = DB::table('data_card')->where(['transid' => $request->transid])->first();
                    if ($data_card_d->plan_status == 0) {
                        $b = DB::table('user')->where('username', $data_card_d->username)->first();
                        $user_balance = $b->bal;
                        DB::table('user')->where('username', $data_card_d->username)->update(['bal' => $user_balance + $data_card_d->amount]);
                        DB::table('message')->where(['username' => $data_card_d->username, 'transid' => $data_card_d->transid])->update(['plan_status' => 2, 'message' =>  "Data Card Printing Fail", 'oldbal' => $user_balance, 'newbal' => $user_balance - $data_card_d->amount]);
                        DB::table('data_card')->where(['username' => $data_card_d->username, 'transid' => $data_card_d->transid])->update(['plan_status' => 2,  'oldbal' => $user_balance, 'newbal' => $user_balance + $data_card_d->amount]);
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Nothing Can Be Done To This Transaction'
                        ])->setStatusCode(403);
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

    public function RechargeCardRefund(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)])->where(function ($query) {
                    $query->where('type', 'ADMIN')->orwhere('type', 'CUSTOMER');
                });
                if ($check_user->count() > 0) {
                    $recharge_card_d = DB::table('recharge_card')->where(['transid' => $request->transid])->first();
                    if ($recharge_card_d->plan_status == 0) {
                        $b = DB::table('user')->where('username', $recharge_card_d->username)->first();
                        $user_balance = $b->bal;
                        DB::table('user')->where('username', $recharge_card_d->username)->update(['bal' => $user_balance + $recharge_card_d->amount]);
                        DB::table('message')->where(['username' => $recharge_card_d->username, 'transid' => $recharge_card_d->transid])->update(['plan_status' => 2, 'message' =>  "Recharge Card Printing Fail", 'oldbal' => $user_balance, 'newbal' => $user_balance - $recharge_card_d->amount]);
                        DB::table('recharge_card')->where(['username' => $recharge_card_d->username, 'transid' => $recharge_card_d->transid])->update(['plan_status' => 2,  'oldbal' => $user_balance, 'newbal' => $user_balance + $recharge_card_d->amount]);
                    } else {
                        return response()->json([
                            'status' => 403,
                            'message' => 'Nothing Can Be Done To This Transaction'
                        ])->setStatusCode(403);
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

    public function AutoRefundByAdex(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (DB::table('data')->where(['plan_status' => 0])->count() > 0) {
                $data_adex = DB::table('data')->where(['plan_status' => 0])->limit(100)->get();
                foreach ($data_adex as $trans) {
                    if (strtolower($trans->wallet) == 'wallet') {
                        $b = DB::table('user')->where('username', $trans->username)->first();
                        $user_balance = $b->bal;
                        DB::table('user')->where('username', $trans->username)->update(['bal' => $user_balance + $trans->amount]);
                    } else {
                        $wallet_bal = strtolower($trans->wallet) . "_bal";
                        $b = DB::table('wallet_funding')->where('username', $trans->username)->first();
                        $user_balance = $b->$wallet_bal;
                        DB::table('wallet_funding')->where('username', $trans->username)->update([$wallet_bal => $user_balance + $trans->amount]);
                    }
                    DB::table('data')->where(['username' => $trans->username, 'transid' => $trans->transid])->delete();
                    DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->delete();
                    $data_new = [
                        'plan_status' => 2,
                        'oldbal' => $user_balance,
                        'newbal' => $user_balance + $trans->amount,
                        'network' => $trans->network,
                        'network_type' => $trans->network_type,
                        'plan_name' => $trans->plan_name,
                        'amount' => $trans->amount,
                        'transid' => $trans->transid,
                        'plan_phone' => $trans->plan_phone,
                        'plan_date' => $this->system_date(),
                        'system' => $trans->system,
                        'wallet' => $trans->wallet,
                        'api_response' => null,
                        'username' => $trans->username
                    ];
                    $message_new = [
                        'plan_status' => 2,
                        'message' =>  "Transaction Fail (Refund)" . $trans->network . ' ' . $trans->plan_name . ' to ' . $trans->plan_phone,
                        'oldbal' => $user_balance,
                        'newbal' => $user_balance + $trans->amount,
                        'username' => $trans->username,
                        'adex_date' => $this->system_date(),
                        'transid' => $trans->transid,
                        'role' => 'data',
                        'amount' => $trans->amount
                    ];
                    DB::table('message')->insert($message_new);
                    DB::table('data')->insert($data_new);

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $b->webhook);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['status' => 'fail', 'request-id' => $trans->transid, 'response' => "Transaction Fail (Refund)" . $trans->network . ' ' . $trans->plan_name . ' to ' . $trans->plan_phone]));  //Post Fields
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_exec($ch);
                    curl_close($ch);
                }

                return 'success';
            } else {
                return 'all done';
            }
        } else {
            return redirect(env('ERROR_500'));
            return response()->json([
                'status' => 403,
                'message' => 'Unable to Authenticate System'
            ])->setStatusCode(403);
        }
    }
    public function AutoSuccessByAdex(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (DB::table('data')->where(['plan_status' => 0])->count() > 0) {
                $data_adex = DB::table('data')->where(['plan_status' => 0])->get();
                foreach ($data_adex as $trans) {
                    $b = DB::table('user')->where(['username' => $trans->username])->first();
                    DB::table('message')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1, 'message' =>   "You have successfully purchased " . $trans->network . ' ' . $trans->plan_name . ' to ' . $trans->plan_phone]);
                    DB::table('data')->where(['username' => $trans->username, 'transid' => $trans->transid])->update(['plan_status' => 1]);

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $b->webhook);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['status' => 'success', 'request-id' => $trans->transid, 'response' => "Transaction Successful" . $trans->network . ' ' . $trans->plan_name . ' to ' . $trans->plan_phone]));  //Post Fields
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_exec($ch);
                    curl_close($ch);
                }
                return 'success';
            } else {
                return 'all done';
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
