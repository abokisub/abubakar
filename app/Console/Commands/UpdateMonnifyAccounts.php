<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UpdateMonnifyAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:monnify-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign real Monnify (WEMA) virtual accounts to users who do not already have one.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting Monnify (WEMA) account assignment for users without one...');
        $users = DB::table('user')->whereNull('wema')->orWhere('wema', '')->get();
        foreach ($users as $user) {
            try {
                $adex_key = DB::table('adex_key')->first();
                $base_monnify = base64_encode($adex_key->mon_app_key . ':' . $adex_key->mon_sk_key);
                // Step 1: Get access token
                $response = Http::withHeaders([
                    'Authorization' => 'Basic ' . $base_monnify,
                ])->post('https://api.monnify.com/api/v1/auth/login');
                if (!$response->successful()) {
                    $this->error("Failed to get Monnify token for user {$user->username}");
                    continue;
                }
                $access_token = $response->json()['responseBody']['accessToken'] ?? null;
                if (!$access_token) {
                    $this->error("No access token for user {$user->username}");
                    continue;
                }
                // Step 2: Create reserved account
                $payload = [
                    "accountReference" => "user-{$user->id}-" . uniqid(),
                    "accountName" => $user->username,
                    "currencyCode" => "NGN",
                    "contractCode" => $adex_key->mon_con_num,
                    "customerEmail" => $user->email,
                    "customerName" => $user->username,
                    "getAllAvailableBanks" => true
                ];
                $this->line("Payload for user {$user->username}: " . json_encode($payload));
                $account_response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'application/json',
                ])->post('https://api.monnify.com/api/v2/bank-transfer/reserved-accounts', $payload);
                if ($account_response->successful()) {
                    $account_data = $account_response->json()['responseBody'] ?? null;
                    $moniepoint_account = null;
                    if ($account_data && isset($account_data['accounts'])) {
                        foreach ($account_data['accounts'] as $acc) {
                            if (strtolower($acc['bankName']) == 'moniepoint microfinance bank') {
                                $moniepoint_account = $acc['accountNumber'];
                                break;
                            }
                        }
                    }
                    if ($moniepoint_account) {
                        DB::table('user')->where('id', $user->id)->update(['wema' => $moniepoint_account]);
                        $this->info("Assigned Moniepoint account {$moniepoint_account} to user {$user->username}");
                    } else {
                        $this->warn("No Moniepoint account found for user {$user->username}");
                    }
                } else {
                    $errorMsg = $account_response->json()['responseMessage'] ?? $account_response->body();
                    $this->error("Failed to create Monnify account for user {$user->username}: {$errorMsg}");
                }
            } catch (\Exception $e) {
                $this->error("Exception for user {$user->username}: " . $e->getMessage());
            }
        }
        $this->info('Monnify (WEMA) account assignment complete.');
        return 0;
    }
} 