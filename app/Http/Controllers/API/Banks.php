<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class  Banks extends Controller
{
 public function GetBanksArray(Request $request)
{
       $allowedOrigins = array_filter(array_map('trim', explode(',', env('ADEX_APP_KEY', ''))));
       $origin = rtrim($request->header('Origin', ''), '/');
       $authorization = $request->header('Authorization');
       
       // Normalize origins for comparison (remove trailing slashes)
       $normalizedOrigins = array_map(function($url) {
           return rtrim($url, '/');
       }, $allowedOrigins);
       
       if (in_array($origin, $normalizedOrigins) || env('ADEX_DEVICE_KEY') === $authorization) {
        if (!empty($request->id)) {
           $auth_user = DB::table('user')->where('status', 1)->where(function($query) use ($request) {
           $query->orWhere('id', $this->verifytoken($request->id))
              ->orWhere('id', $this->verifyapptoken($request->id));
           })->first();

            $setting = $this->core();
            $banks_array = [];
            if(!$auth_user){
                return response()->json(['message' => 'Unable to singin user', 'status' => 'fail'], 403);
            }
            
            // BELLBANK - Default and Primary Payment Gateway (Always First)
            $bellAccount = \App\Models\BellAccount::where('user_id', $auth_user->id)
                ->where('status', 'active')
                ->first();
            
            if ($bellAccount && $bellAccount->account_number) {
                // Get virtual account charge from settings
                $virtualAccountCharge = \App\Models\BellSetting::getValue('virtual_account_charge', '0');
                $chargeAmount = (float) $virtualAccountCharge;
                $chargesDisplay = $chargeAmount > 0 
                    ? '₦' . number_format($chargeAmount, 2) . ' NAIRA' 
                    : '0 NAIRA';
                
                $banks_array[] = [
                    "name" => $bellAccount->bank_name ?: "BELLBANK",
                    "account" => $bellAccount->account_number,
                    "accountType" => false, // Account is ready
                    'charges' => $chargesDisplay,
                    'isDefault' => true, // Mark as default
                    'accountName' => $auth_user->username, // User's account name
                ];
            }
            
            // Use dynamic charges from settings
            $palmpay_charge = isset($setting->palmpay_charge) ? $setting->palmpay_charge : 15;
            $monnify_charge = isset($setting->monnify_charge) ? $setting->monnify_charge : 20;
            
            // Other payment methods (secondary/optional)
            if (!is_null($auth_user->palmpay)) {
                $banks_array[] = [
                    "name" => "PALMPAY",
                    "account" => $auth_user->palmpay,
                    "accountType" => $auth_user->palmpay === null,
                    'charges' => $palmpay_charge . ' NAIRA',
                    'isDefault' => false,
                ];
            }
            // Only add Moniepoint if it is not hardcoded as WEMA
            if (!is_null($auth_user->wema)) {
                $banks_array[] = [
                    "name" => "MONIEPOINT",
                    "account" => $auth_user->wema,
                    "accountType" => $auth_user->wema === null,
                    'charges' => $monnify_charge . ' NAIRA',
                    'isDefault' => false,
                ];
            }
            if (!is_null($auth_user->paystack_account)) {
                $banks_array[] = [
                    "name" => "PAYSTACK",
                    "account" => $auth_user->paystack_account,
                    "accountType" => false,
                    'charges' => '0 NAIRA', // You can make this dynamic if needed
                    'isDefault' => false,
                ];
            }

            return response()->json(['status' => 'success', 'banks' => $banks_array]);
        } else {
            return response()->json(['status' => 'fail', 'message' => 'Hey,Login To Continue'])->setStatusCode(403);
        }
    } else {
        return response()->json(['status' => 'fail', 'message' => 'Cannot Retrieve Banks'])->setStatusCode(403);
    }
}


}