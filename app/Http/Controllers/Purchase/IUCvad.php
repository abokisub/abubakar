<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class  IUCvad extends Controller
{
    public function IUC(Request $request)
    {
        if ((isset($request->iuc)) and (!empty($request->iuc))) {
            if ((isset($request->cable)) and (!empty($request->cable))) {
                if (DB::table('cable_id')->where('plan_id', $request->cable)->count() == 1) {
                    $cable = DB::table('cable_id')->where('plan_id', $request->cable)->first();
                    $cable_sel = DB::table('cable_sel')->first();
                    $adm = new IUCsend();
                    $cable_name = strtolower($cable->cable_name);
                    $check_now = $cable_sel->$cable_name;
                    $sending_data = [
                        'iuc' => $request->iuc,
                        'cable' => $request->cable
                    ];
                    $response = $adm->$check_now($sending_data);
                    if (!empty($response)) {
                        return response()->json([
                            'status' => 'success',
                            'name' => $response
                        ]);
                    } else {
                        return response()->json([
                            'status' => 'fail',
                            'message' => 'Invalid IUC NUMBER'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'status' => 'fail',
                        'message' => 'inavlid cable id'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'cable id required'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'iuc number required'
            ])->setStatusCode(403);
        }
    }
}
