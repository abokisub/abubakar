<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Http;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public  function core()
    {
        $sets = DB::table('settings');
        if ($sets->count() == 1) {
            return $sets->first();
        } else {
            return null;
        }
    }

    public  function  adex_key()
    {
        $sets = DB::table('adex_key');
        if ($sets->count() == 1) {
            return $sets->first();
        } else {
            return null;
        }
    }

    public  function  general()
    {
        $sets = DB::table('general');
        if ($sets->count() == 1) {
            return $sets->first();
        } else {
            return null;
        }
    }

    public function feature()
    {
        return  DB::table('feature')->get();
    }


    public  function updateData($data, $tablename, $tableid)
    {
        return  DB::table($tablename)
            ->where($tableid)
            ->update($data);
    }


    public  function generatetoken($req)
    {
        if (DB::table('user')->where('id', $req)->count() == 1) {
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $pin =  mt_rand(1000000, 9999999)
                . mt_rand(1000000, 9999999)
                . $characters[rand(0, strlen($characters) - 1)];
            $secure_key = str_shuffle($pin);
            DB::table('user')->where('id', $req)->update(['adex_key' => $secure_key]);
            return $secure_key;
        } else {
            return null;
        }
    }

    public function generateapptoken($key)
    {
        if (DB::table('user')->where('id', $key)->count() == 1) {
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $pin =  mt_rand(1000000, 9999999)
                . mt_rand(1000000, 9999999)
                . $characters[rand(0, strlen($characters) - 1)];
            $secure_key = str_shuffle($pin);
            DB::table('user')->where('id', $key)->update(['app_key' => $secure_key]);
            return $secure_key;
        } else {
            return null;
        }
    }
    public function verifyapptoken($key)
    {
        if (DB::table('user')->where('app_key', $key)->count() == 1) {
            $user = DB::table('user')->where('app_key', $key)->first();
            return $user->id;
        } else {
            return null;
        }
    }

    public  function verifytoken($request)
    {
        if (DB::table('user')->where('adex_key', $request)->count() == 1) {
            $user = DB::table('user')->where('adex_key', $request)->first();
            return $user->id;
        } else {
            return null;
        }
    }


    public  function generate_ref($title)
    {
        $code = random_int(100000, 999999);
        $me = random_int(1000, 9999);
        $app_name = env('APP_NAME');
        $ref = "|$app_name|$title|$code|adex-dev-$me";
        return $ref;
    }
    public function purchase_ref($d)
    {
        return uniqid($d);
    }
    public  function insert_stock($username)
    {
        $check_first = DB::table('wallet_funding')->where('username', $username);
        if ($check_first->count() == 0) {
            $values = array('username' => $username);
            DB::table('wallet_funding')->insert($values);
        }
    }
    public function inserting_data($table, $data)
    {
        return DB::table($table)->insert($data);
    }
    public  function monnify_account($username)
    {
        $this_Controller =   new Controller;
        $check_first = DB::table('user')->where('username', $username);
        if ($check_first->count() == 1) {
            $get_user = $check_first->get()[0];
            $setting =  $this_Controller->core();
            $adex_key = $this_Controller->adex_key();
  
if(is_null($get_user->palmpay)){
      $response = Http::withHeaders([
          'Authorization' => 'Bearer c095833106da5b4dcfaa0ca1329e491f48408f1ba795fbeb9f1b76168950ae02d7afff6b60f55e7fc4b996b259abdb6c3464ed30b881262fe9af64f8',
          'api-key' => 'f1d804a2474191a538cbd4fc85cecc44bfb42bf6'
          ])->post('https://api.xixapay.com/api/v1/createVirtualAccount', [
              'email' => $get_user->email,
              'name' => $get_user->username,
              'phoneNumber' => $get_user->phone,
              'bankCode' => ['20867'],
              'businessId' => 'b11c7d175f1d20fccd2441aec0e2fb7055a933d0'
              ]);
              file_put_contents('response_h.json', json_encode($response->json()));
      if($response->successful()){
         $data = $response->json();
         if(isset($data['bankAccounts'])){
             foreach($data['bankAccounts'] as $bank){
                 if($bank['bankCode'] == '20867'){
                     DB::table('user')->where('id', $get_user->id)->update(['palmpay' => $bank['accountNumber']]);
                 }
             }
         }
      }
   }

 }
}    public function system_date()
    {
        return  Carbon::now("Africa/Lagos")->toDateTimeString();
    }

    public function paystack_account($username)
    {
        $user = DB::table('user')->where('username', $username)->first();
        if (!$user) {
            \Log::error('Paystack: User not found for username: ' . $username);
            return false;
        }
        $adex_key = $this->adex_key();
        $paystack_secret = $adex_key->psk ?? env('PAYSTACK_SECRET_KEY');
        if (!$paystack_secret) {
            \Log::error('Paystack: Secret key missing for user: ' . $username);
            return false;
        }
        // Only create if not already assigned
        if ($user->paystack_account && $user->paystack_bank) {
            \Log::info('Paystack: Account already exists for user: ' . $username);
            return true;
        }
        // Step 1: Create customer if not exists
        $customerPayload = [
            'email' => $user->email,
            'first_name' => $user->username,
            'phone' => $user->phone,
        ];
        $customerResponse = Http::withToken($paystack_secret)
            ->post('https://api.paystack.co/customer', $customerPayload);
        \Log::info('Paystack: Customer API response for user ' . $username . ': ' . json_encode($customerResponse->json()));
        if ($customerResponse->successful() && isset($customerResponse['data']['customer_code'])) {
            $customer_code = $customerResponse['data']['customer_code'];
        } elseif (isset($customerResponse['data']['customer_code'])) {
            $customer_code = $customerResponse['data']['customer_code'];
        } else {
            \Log::error('Paystack: Failed to create/find customer for user: ' . $username . '. Response: ' . $customerResponse->body());
            return false;
        }
        // Step 2: Create dedicated account
        $full_name = isset($user->name) && $user->name ? $user->name : $user->username;
        $name_parts = preg_split('/\s+/', trim($full_name));
        $first_name = $name_parts[0];
        $last_name = count($name_parts) > 1 ? $name_parts[count($name_parts) - 1] : $name_parts[0];
        $phone = $user->phone;
        $accountPayload = [
            'customer' => $customer_code,
            'provider_slug' => 'titan-paystack',
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone,
        ];
        $accountResponse = Http::withToken($paystack_secret)
            ->post('https://api.paystack.co/dedicated_account', $accountPayload);
        \Log::info('Paystack: Dedicated Account API response for user ' . $username . ': ' . json_encode($accountResponse->json()));
        if ($accountResponse->successful() && isset($accountResponse['data']['account_number'])) {
            $acc = $accountResponse['data'];
            DB::table('user')->where('id', $user->id)->update([
                'paystack_account' => $acc['account_number'],
                'paystack_bank' => $acc['bank']['name'] ?? 'Paystack',
            ]);
            \Log::info('Paystack: Account assigned for user: ' . $username);
            return true;
        } else {
            \Log::error('Paystack: Failed to assign account for user: ' . $username . '. Response: ' . $accountResponse->body());
        }
        return false;
    }
}
