<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class  Trans extends Controller
{

    public function UserTrans(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (DB::table('user')->where(['id' => $this->verifytoken($request->id), 'status' => 1])->count() == 1) {
                $user = DB::table('user')->where(['id' => $this->verifytoken($request->id), 'status' => 1])->first();
                $search = strtolower($request->search);
                $database_name = strtolower($request->database_name);
                if ($database_name === 'bank_trans') {
                    if (!empty($search)) {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'bank_trans' => DB::table('bank_transfer')->where('username', $user->username)->Where(function ($query) use ($search) {
                                    $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('date', 'LIKE', "%$search%")->orWhere('account_name', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('account_number', 'LIKE', "%$search%")->orWhere('bank_name', 'LIKE', "%$search%")->orWhere('bank_code', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'bank_trans' => DB::table('bank_transfer')->where(['username' => $user->username, 'plan_status' => $request->status])->Where(function ($query) use ($search) {
                                    $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('date', 'LIKE', "%$search%")->orWhere('account_name', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('account_number', 'LIKE', "%$search%")->orWhere('bank_name', 'LIKE', "%$search%")->orWhere('bank_code', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        }
                    } else {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'bank_trans' => DB::table('bank_transfer')->where('username', $user->username)->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'bank_trans' => DB::table('bank_transfer')->where(['username' => $user->username, 'plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        }
                    }
                } else if ($database_name == 'cable_trans') {
                    if (!empty($search)) {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'cable_trans' => DB::table('cable')->where('username', $user->username)->Where(function ($query) use ($search) {
                                    $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('charges', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('cable_plan', 'LIKE', "%$search%")->orWhere('cable_name', 'LIKE', "%$search%")->orWhere('iuc', 'LIKE', "%$search%")->orWhere('customer_name', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'cable_trans' => DB::table('cable')->where(['username' => $user->username, 'plan_status' => $request->status])->Where(function ($query) use ($search) {
                                    $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('charges', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('cable_plan', 'LIKE', "%$search%")->orWhere('cable_name', 'LIKE', "%$search%")->orWhere('iuc', 'LIKE', "%$search%")->orWhere('customer_name', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        }
                    } else {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'cable_trans' => DB::table('cable')->where('username', $user->username)->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'cable_trans' => DB::table('cable')->where(['username' => $user->username, 'plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        }
                    }
                } elseif ($database_name == 'bill_trans') {
                    if (!empty($search)) {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'bill_trans' => DB::table('bill')->where('username', $user->username)->Where(function ($query) use ($search) {
                                    $query->orWhere('disco_name', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('meter_number', 'LIKE', "%$search%")->orWhere('meter_type', 'LIKE', "%$search%")->orWhere('customer_name', 'LIKE', "%$search%")->orWhere('token', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'bill_trans' => DB::table('bill')->where(['username' => $user->username, 'plan_status' => $request->status])->Where(function ($query) use ($search) {
                                    $query->orWhere('disco_name', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('meter_number', 'LIKE', "%$search%")->orWhere('meter_type', 'LIKE', "%$search%")->orWhere('customer_name', 'LIKE', "%$search%")->orWhere('token', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        }
                    } else {

                        if ($request->status == 'ALL') {
                            return response()->json([
                                'bill_trans' => DB::table('bill')->where('username', $user->username)->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'bill_trans' => DB::table('bill')->where(['username' => $user->username, 'plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        }
                    }
                } else if ($database_name == 'bulksms_trans') {
                    if (!empty($search)) {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'bulksms_trans' => DB::table('bulksms')->where('username', $user->username)->Where(function ($query) use ($search) {
                                    $query->orWhere('correct_number', 'LIKE', "%$search%")->orWhere('wrong_number', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('total_correct_number', 'LIKE', "%$search%")->orWhere('total_wrong_number', 'LIKE', "%$search%")->orWhere('message', 'LIKE', "%$search%")->orWhere('sender_name', 'LIKE', "%$search%")->orWhere('numbers', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'bulksms_trans' => DB::table('bulksms')->where(['username' => $user->username, 'plan_status' => $request->status])->Where(function ($query) use ($search) {
                                    $query->orWhere('correct_number', 'LIKE', "%$search%")->orWhere('wrong_number', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('total_correct_number', 'LIKE', "%$search%")->orWhere('total_wrong_number', 'LIKE', "%$search%")->orWhere('message', 'LIKE', "%$search%")->orWhere('sender_name', 'LIKE', "%$search%")->orWhere('numbers', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        }
                    } else {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'bulksms_trans' => DB::table('bulksms')->where('username', $user->username)->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'bulksms_trans' => DB::table('bulksms')->where(['username' => $user->username, 'plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        }
                    }
                } else if ($database_name == 'cash_trans') {
                    if (!empty($search)) {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'cash_trans' => DB::table('cash')->where('username', $user->username)->Where(function ($query) use ($search) {
                                    $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('amount_credit', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('payment_type', 'LIKE', "%$search%")->orWhere('network', 'LIKE', "%$search%")->orWhere('sender_number', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'cash_trans' => DB::table('cash')->where(['username' => $user->username, 'plan_status' => $request->status])->Where(function ($query) use ($search) {
                                    $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('amount_credit', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('payment_type', 'LIKE', "%$search%")->orWhere('network', 'LIKE', "%$search%")->orWhere('sender_number', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        }
                    } else {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'cash_trans' => DB::table('cash')->where('username', $user->username)->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'cash_trans' => DB::table('cash')->where(['username' => $user->username, 'plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        }
                    }
                } else if ($database_name == 'result_trans') {
                    if (!empty($search)) {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'result_trans' => DB::table('exam')->where(['username' => $user->username])->Where(function ($query) use ($search) {
                                    $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('purchase_code', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('exam_name', 'LIKE', "%$search%")->orWhere('quantity', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'result_trans' => DB::table('exam')->where(['username' => $user->username, 'plan_status' => $request->status])->Where(function ($query) use ($search) {
                                    $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('purchase_code', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('exam_name', 'LIKE', "%$search%")->orWhere('quantity', 'LIKE', "%$search%")->orWhere('username', 'LIKE', "%$search%");
                                })->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        }
                    } else {
                        if ($request->status == 'ALL') {
                            return response()->json([
                                'result_trans' => DB::table('exam')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate($request->adex)
                            ]);
                        } else {
                            return response()->json([
                                'result_trans' => DB::table('exam')->where(['username' => $user->username, 'plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
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
                    'message' => 'Access Denail'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'status' => 403,
                'message' => 'Access Denail'
            ])->setStatusCode(403);
        }
    }

    public function AllDepositHistory(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (DB::table('user')->where(['id' => $this->verifytoken($request->id), 'status' => 1])->count() == 1) {
                $user = DB::table('user')->where(['id' => $this->verifytoken($request->id), 'status' => 1])->first();
                $search = strtolower($request->search);
                if (!empty($search)) {
                    if ($request->status == 'ALL') {
                        return response()->json([
                            'deposit_trans' => DB::table('deposit')->where(['username' => $user->username])->Where(function ($query) use ($search) {
                                $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('wallet_type', 'LIKE', "%$search%")->orWhere('type', 'LIKE', "%$search%")->orWhere('credit_by', 'LIKE', "%$search%")->orWhere('charges', 'LIKE', "%$search%")->orWhere('monify_ref', 'LIKE', "%$search%");
                            })->orderBy('id', 'desc')->paginate($request->adex),
                        ]);
                    } else {
                        return response()->json([
                            'deposit_trans' => DB::table('deposit')->where(['username' => $user->username, 'status' => $request->status])->Where(function ($query) use ($search) {
                                $query->orWhere('amount', 'LIKE', "%$search%")->orWhere('date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('wallet_type', 'LIKE', "%$search%")->orWhere('type', 'LIKE', "%$search%")->orWhere('credit_by', 'LIKE', "%$search%")->orWhere('charges', 'LIKE', "%$search%")->orWhere('monify_ref', 'LIKE', "%$search%");
                            })->orderBy('id', 'desc')->paginate($request->adex)
                        ]);
                    }
                } else {
                    if ($request->status == 'ALL') {
                        return response()->json([
                            'deposit_trans' => DB::table('deposit')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate($request->adex),
                        ]);
                    } else {
                        return response()->json([
                            'deposit_trans' => DB::table('deposit')->where(['username' => $user->username, 'status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                        ]);
                    }
                }
            } else {
                return response()->json([
                    'status' => 403,
                    'message' => 'Access Denail'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'status' => 403,
                'message' => 'Access Denail'
            ])->setStatusCode(403);
        }
    }

    public function AllHistoryUser(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (DB::table('user')->where(['id' => $this->verifytoken($request->id), 'status' => 1])->count() == 1) {
                $user = DB::table('user')->where(['id' => $this->verifytoken($request->id), 'status' => 1])->first();
                $search = strtolower($request->search);
                
                // Get messages (existing transactions)
                $messageQuery = DB::table('message')->where(['username' => $user->username]);
                if ($request->status != 'ALL') {
                    $messageQuery->where('plan_status', $request->status);
                }
                if (!empty($search)) {
                    $messageQuery->where(function ($query) use ($search) {
                        $query->orWhere('message', 'LIKE', "%$search%")
                              ->orWhere('adex_date', 'LIKE', "%$search%")
                              ->orWhere('oldbal', 'LIKE', "%$search%")
                              ->orWhere('transid', 'LIKE', "%$search%")
                              ->orWhere('newbal', 'LIKE', "%$search%");
                    });
                }
                $messages = $messageQuery->get()->map(function($msg) {
                    return [
                        'transid' => $msg->transid,
                        'message' => $msg->message,
                        'amount' => $msg->amount ?? 0,
                        'oldbal' => $msg->oldbal ?? 0,
                        'newbal' => $msg->newbal ?? 0,
                        'adex_date' => $msg->adex_date ?? now()->format('Y-m-d H:i:s'),
                        'plan_status' => $msg->plan_status ?? 0,
                        'type' => 'message',
                    ];
                });

                // Get message transids to exclude duplicates
                $messageTransids = DB::table('message')
                    ->where('username', $user->username)
                    ->pluck('transid')
                    ->toArray();
                
                // Get BellBank transfers that don't have message entries yet
                // (message entries are created for successful transfers, so we only need pending/failed ones)
                $bellTransferQuery = DB::table('bell_transactions')
                    ->where('user_id', $user->id)
                    ->where('type', 'transfer');
                
                // Exclude transfers that already have message entries
                if (!empty($messageTransids)) {
                    $bellTransferQuery->whereNotIn('reference', $messageTransids);
                }
                
                // Map BellBank status to message plan_status format
                // success/successful = 1, pending = 0, failed = 2
                if ($request->status != 'ALL') {
                    if ($request->status == 1) {
                        $bellTransferQuery->whereIn('status', ['success', 'successful']);
                    } elseif ($request->status == 0) {
                        $bellTransferQuery->where('status', 'pending');
                    } elseif ($request->status == 2) {
                        $bellTransferQuery->whereIn('status', ['failed', 'failure']);
                    }
                }
                
                if (!empty($search)) {
                    $bellTransferQuery->where(function ($query) use ($search) {
                        $query->orWhere('reference', 'LIKE', "%$search%")
                              ->orWhere('description', 'LIKE', "%$search%")
                              ->orWhere('amount', 'LIKE', "%$search%")
                              ->orWhere('destination_account_number', 'LIKE', "%$search%")
                              ->orWhere('destination_bank_name', 'LIKE', "%$search%");
                    });
                }
                
                $bellTransfers = $bellTransferQuery->get()->map(function($trans) use ($user) {
                    // Map BellBank status to plan_status
                    $planStatus = 0; // pending
                    if (in_array($trans->status, ['success', 'successful'])) {
                        $planStatus = 1; // success
                    } elseif (in_array($trans->status, ['failed', 'failure'])) {
                        $planStatus = 2; // failed
                    }
                    
                    // For transfers without message entries, estimate balances
                    // (These are usually pending or failed transfers that haven't been processed yet)
                    $totalDeducted = $trans->amount + ($trans->charge ?? 0);
                    $oldBalance = $user->bal;
                    $newBalance = $planStatus === 1 ? ($oldBalance - $totalDeducted) : $oldBalance;
                    
                    return [
                        'transid' => $trans->reference,
                        'message' => 'Bank Transfer to ' . ($trans->destination_account_name ?? $trans->destination_account_number) . ' - ' . ($trans->destination_bank_name ?? ''),
                        'amount' => $trans->amount,
                        'oldbal' => $oldBalance,
                        'newbal' => $newBalance,
                        'adex_date' => $trans->created_at,
                        'plan_status' => $planStatus,
                        'type' => 'bellbank_transfer',
                        'bellbank_data' => [
                            'reference' => $trans->reference,
                            'destination_account_number' => $trans->destination_account_number,
                            'destination_account_name' => $trans->destination_account_name,
                            'destination_bank_name' => $trans->destination_bank_name,
                            'destination_bank_code' => $trans->destination_bank_code,
                            'charge' => $trans->charge ?? 0,
                            'net_amount' => $trans->net_amount,
                            'status' => $trans->status,
                            'description' => $trans->description,
                            'created_at' => $trans->created_at,
                        ],
                    ];
                });

                // Merge and sort by date
                $allTransactions = $messages->concat($bellTransfers);
                
                // Sort by date (most recent first)
                $allTransactions = $allTransactions->sortByDesc(function($item) {
                    return strtotime($item['adex_date'] ?? '1970-01-01');
                })->values();

                // Paginate manually
                $page = (int) $request->get('page', 1);
                $perPage = (int) $request->get('adex', 5);
                $total = $allTransactions->count();
                $offset = ($page - 1) * $perPage;
                $paginated = $allTransactions->slice($offset, $perPage)->values();

                return response()->json([
                    'all_summary' => [
                        'data' => $paginated->all(),
                        'total' => $total,
                        'per_page' => $perPage,
                        'current_page' => $page,
                        'last_page' => $perPage > 0 ? ceil($total / $perPage) : 1,
                    ]
                ], 200, [], JSON_NUMERIC_CHECK);
            } else {
                return response()->json([
                    'status' => 403,
                    'message' => 'Access Denail'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'status' => 403,
                'message' => 'Access Denail'
            ])->setStatusCode(403);
        }
    }
    public function AllAirtimeUser(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (DB::table('user')->where(['id' => $this->verifytoken($request->id), 'status' => 1])->count() == 1) {
                $user = DB::table('user')->where(['id' => $this->verifytoken($request->id), 'status' => 1])->first();
                $search = strtolower($request->search);
                if (!empty($search)) {
                    if ($request->status == 'ALL') {
                        return response()->json([
                            'airtime_trans' => DB::table('airtime')->where(['username' => $user->username])->Where(function ($query) use ($search) {
                                $query->orWhere('network', 'LIKE', "%$search%")->orWhere('network_type', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('discount', 'LIKE', "%$search%")->orWhere('plan_phone', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%");
                            })->orderBy('id', 'desc')->paginate($request->adex)
                        ]);
                    } else {
                        return response()->json([
                            'airtime_trans' => DB::table('airtime')->where(['username' => $user->username, 'plan_status' => $request->status])->Where(function ($query) use ($search) {
                                $query->orWhere('network', 'LIKE', "%$search%")->orWhere('network_type', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('discount', 'LIKE', "%$search%")->orWhere('plan_phone', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%");
                            })->orderBy('id', 'desc')->paginate($request->adex)
                        ]);
                    }
                } else {
                    if ($request->status == 'ALL') {
                        return response()->json([
                            'airtime_trans' => DB::table('airtime')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate($request->adex)
                        ]);
                    } else {
                        return response()->json([
                            'airtime_trans' => DB::table('airtime')->where(['username' => $user->username, 'plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                        ]);
                    }
                }
            } else {
                return response()->json([
                    'status' => 403,
                    'message' => 'Access Denail'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'status' => 403,
                'message' => 'Access Denail'
            ])->setStatusCode(403);
        }
    }
    public function AllStockHistoryUser(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (DB::table('user')->where(['id' => $this->verifytoken($request->id), 'status' => 1])->count() == 1) {
                $user = DB::table('user')->where(['id' => $this->verifytoken($request->id), 'status' => 1])->first();
                $search = strtolower($request->search);

                if (!empty($search)) {
                    if ($request->status == 'ALL') {
                        return response()->json([
                            'data_trans' => DB::table('data')->where('username', $user->username)->where('wallet', '!=', 'wallet')->Where(function ($query) use ($search) {
                                $query->orWhere('network', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('api_response', 'LIKE', "%$search%")->orWhere('plan_phone', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('network_type', 'LIKE', "%$search%")->orWhere('wallet', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%");
                            })->orderBy('id', 'desc')->paginate($request->adex)
                        ]);
                    } else {
                        return response()->json([
                            'data_trans' => DB::table('data')->where(['username' => $user->username, 'plan_status' => $request->status])->where('wallet', '!=', 'wallet')->Where(function ($query) use ($search) {
                                $query->orWhere('network', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('api_response', 'LIKE', "%$search%")->orWhere('plan_phone', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('network_type', 'LIKE', "%$search%")->orWhere('wallet', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%");
                            })->orderBy('id', 'desc')->paginate($request->adex)
                        ]);
                    }
                } else {
                    if ($request->status == 'ALL') {
                        return response()->json([
                            'data_trans' => DB::table('data')->where('username', $user->username)->where('wallet', '!=', 'wallet')->orderBy('id', 'desc')->paginate($request->adex)
                        ]);
                    } else {
                        return response()->json([
                            'data_trans' => DB::table('data')->where('wallet', '!=', 'wallet')->where(['username' => $user->username, 'plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                        ]);
                    }
                }
            } else {
                return response()->json([
                    'status' => 403,
                    'message' => 'Access Denail'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'status' => 403,
                'message' => 'Access Denail'
            ])->setStatusCode(403);
        }
    }

    public function AllDataHistoryUser(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (DB::table('user')->where(['id' => $this->verifytoken($request->id), 'status' => 1])->count() == 1) {
                $user = DB::table('user')->where(['id' => $this->verifytoken($request->id), 'status' => 1])->first();
                $search = strtolower($request->search);

                if (!empty($search)) {
                    if ($request->status == 'ALL') {
                        return response()->json([
                            'data_trans' => DB::table('data')->where('username', $user->username)->Where(function ($query) use ($search) {
                                $query->orWhere('network', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('api_response', 'LIKE', "%$search%")->orWhere('plan_phone', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('network_type', 'LIKE', "%$search%")->orWhere('wallet', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%");
                            })->orderBy('id', 'desc')->paginate($request->adex)
                        ]);
                    } else {
                        return response()->json([
                            'data_trans' => DB::table('data')->where(['username' => $user->username, 'plan_status' => $request->status])->Where(function ($query) use ($search) {
                                $query->orWhere('network', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('oldbal', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('api_response', 'LIKE', "%$search%")->orWhere('plan_phone', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('network_type', 'LIKE', "%$search%")->orWhere('wallet', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%");
                            })->orderBy('id', 'desc')->paginate($request->adex)
                        ]);
                    }
                } else {
                    if ($request->status == 'ALL') {
                        return response()->json([
                            'data_trans' => DB::table('data')->where('username', $user->username)->orderBy('id', 'desc')->paginate($request->adex)
                        ]);
                    } else {
                        return response()->json([
                            'data_trans' => DB::table('data')->where(['username' => $user->username, 'plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                        ]);
                    }
                }
            } else {
                return response()->json([
                    'status' => 403,
                    'message' => 'Access Denail'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'status' => 403,
                'message' => 'Access Denail'
            ])->setStatusCode(403);
        }
    }

    public function DataTrans(Request $request)
    {
        if (DB::table('data')->where(['transid' => $request->id])->count() == 1) {
            return response()->json([
                'trans' => DB::table('data')->where(['transid' => $request->id])->first()
            ]);
        } else {
            return response()->json([
                'message' => 'Not Available'
            ])->setStatusCode(403);
        }
    }
    public function AirtimeTrans(Request $request)
    {
        if (DB::table('airtime')->where(['transid' => $request->id])->count() == 1) {
            return response()->json([
                'trans' => DB::table('airtime')->where(['transid' => $request->id])->first()
            ]);
        } else {
            return response()->json([
                'message' => 'Not Available'
            ])->setStatusCode(403);
        }
    }
    public function DepositTrans(Request $request)
    {
        if (DB::table('deposit')->where(['transid' => $request->id])->count() == 1) {
            return response()->json([
                'trans' => DB::table('deposit')->where(['transid' => $request->id])->first()
            ]);
        } else {
            return response()->json([
                'message' => 'Not Available'
            ])->setStatusCode(403);
        }
    }
    public function CableTrans(Request $request)
    {
        if (DB::table('cable')->where(['transid' => $request->id])->count() == 1) {
            return response()->json([
                'trans' => DB::table('cable')->where(['transid' => $request->id])->first()
            ]);
        } else {
            return response()->json([
                'message' => 'Not Available'
            ])->setStatusCode(403);
        }
    }
    public function BillTrans(Request $request)
    {
        if (DB::table('bill')->where(['transid' => $request->id])->count() == 1) {
            return response()->json([
                'trans' => DB::table('bill')->where(['transid' => $request->id])->first()
            ]);
        } else {
            return response()->json([
                'message' => 'Not Available'
            ])->setStatusCode(403);
        }
    }
    public function AirtimeCashTrans(Request $request)
    {
        if (DB::table('cash')->where(['transid' => $request->id])->count() == 1) {
            return response()->json([
                'trans' => DB::table('cash')->where(['transid' => $request->id])->first()
            ]);
        } else {
            return response()->json([
                'message' => 'Not Available'
            ])->setStatusCode(403);
        }
    }
    public function BulkSMSTrans(Request $request)
    {
        if (DB::table('bulksms')->where(['transid' => $request->id])->count() == 1) {
            return response()->json([
                'trans' => DB::table('bulksms')->where(['transid' => $request->id])->first()
            ]);
        } else {
            return response()->json([
                'message' => 'Not Available'
            ])->setStatusCode(403);
        }
    }
    public function ResultCheckerTrans(Request $request)
    {
        if (DB::table('exam')->where(['transid' => $request->id])->count() == 1) {
            return response()->json([
                'trans' => DB::table('exam')->where(['transid' => $request->id])->first()
            ]);
        } else {
            return response()->json([
                'message' => 'Not Available'
            ])->setStatusCode(403);
        }
    }
    public function ManualTransfer(Request $request)
    {
        if (DB::table('bank_transfer')->where(['transid' => $request->id])->count() == 1) {
            return response()->json([
                'trans' => DB::table('bank_transfer')->where(['transid' => $request->id])->first()
            ]);
        } else {
            return response()->json([
                'message' => 'Not Available'
            ])->setStatusCode(403);
        }
    }

    public function DataCardInvoice(Request $request)
    {
        if (DB::table('data_card')->where(['transid' => $request->id])->count() == 1) {
            return response()->json([
                'trans' => DB::table('data_card')->where(['transid' => $request->id])->first()
            ]);
        } else {
            return response()->json([
                'message' => 'Not Available'
            ])->setStatusCode(403);
        }
    }

    public function DataCardSuccess(Request $request)
    {
        if (DB::table('data_card')->where(['transid' => $request->id])->count() == 1) {
            return response()->json([
                'trans' => DB::table('data_card')->where(['transid' => $request->id])->first(),
                'card_map' => DB::table('dump_data_card_pin')->where(['transid' => $request->id])->get()
            ]);
        } else {
            return response()->json([
                'message' => 'Not Available'
            ])->setStatusCode(403);
        }
    }

    public function DataRechardPrint(Request $request)
    {
        $explode_url = explode(',', env('ADEX_APP_KEY'));
        if (in_array($request->headers->get('origin'), $explode_url)) {
            if (!empty($request->id)) {
                $check_user = DB::table('user')->where(['status' => 1, 'id' => $this->verifytoken($request->id)]);
                if ($check_user->count() > 0) {
                    $user = $check_user->first();
                    $search = strtolower($request->search);
                    $database_name = strtolower($request->database_name);
                    if ($database_name == 'data_card') {
                        if (!empty($search)) {
                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'data_card' => DB::table('data_card')->where(['username' => $user->username])->where(function ($query) use ($search) {
                                        $query->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('load_pin', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('plan_type', 'LIKE', "%$search%")->orWhere('card_name', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'data_card' => DB::table('data_card')->where(['username' => $user->username])->where(function ($query) use ($search) {
                                        $query->orWhere('username', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('load_pin', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('plan_type', 'LIKE', "%$search%")->orWhere('card_name', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%");
                                    })->where(['plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        } else {
                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'data_card' => DB::table('data_card')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'data_card' => DB::table('data_card')->where(['username' => $user->username])->where(['plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        }
                    } else if ($database_name == 'recharge_card') {
                        if (!empty($search)) {
                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'recharge_card' => DB::table('recharge_card')->where(['username' => $user->username])->where(function ($query) use ($search) {
                                        $query->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('load_pin', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('card_name', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%");
                                    })->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'recharge_card' => DB::table('recharge_card')->where(['username' => $user->username])->where(function ($query) use ($search) {
                                        $query->orWhere('username', 'LIKE', "%$search%")->orWhere('plan_date', 'LIKE', "%$search%")->orWhere('load_pin', 'LIKE', "%$search%")->orWhere('transid', 'LIKE', "%$search%")->orWhere('newbal', 'LIKE', "%$search%")->orWhere('system', 'LIKE', "%$search%")->orWhere('card_name', 'LIKE', "%$search%")->orWhere('plan_name', 'LIKE', "%$search%");
                                    })->where(['plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            }
                        } else {
                            if ($request->status == 'ALL') {
                                return response()->json([
                                    'recharge_card' => DB::table('recharge_card')->where(['username' => $user->username])->orderBy('id', 'desc')->paginate($request->adex)
                                ]);
                            } else {
                                return response()->json([
                                    'recharge_card' => DB::table('recharge_card')->where(['username' => $user->username])->where(['plan_status' => $request->status])->orderBy('id', 'desc')->paginate($request->adex)
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

    public function RechargeCardProcess(Request $request)
    {
        if (DB::table('recharge_card')->where(['transid' => $request->id])->count() == 1) {
            return response()->json([
                'trans' => DB::table('recharge_card')->where(['transid' => $request->id])->first()
            ]);
        } else {
            return response()->json([
                'message' => 'Not Available'
            ])->setStatusCode(403);
        }
    }

    public function RechargeCardPrint(Request $request)
    {
        if (DB::table('recharge_card')->where(['transid' => $request->id])->count() == 1) {
            return response()->json([
                'trans' => DB::table('recharge_card')->where(['transid' => $request->id])->first(),
                'card_map' => DB::table('dump_recharge_card_pin')->where(['transid' => $request->id])->get()
            ]);
        } else {
            return response()->json([
                'message' => 'Not Available'
            ])->setStatusCode(403);
        }
    }
}
