<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\BellAccount;
use App\Models\BellKyc;
use App\Services\BellBankService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateBellBankVirtualAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [5, 10, 30]; // Retry after 5s, 10s, 30s

    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(BellBankService $bellBankService)
    {
        try {
            // Check if account already exists
            $existingAccount = BellAccount::where('user_id', $this->user->id)->first();
            
            if ($existingAccount) {
                Log::info('BellBank virtual account already exists for user', [
                    'user_id' => $this->user->id,
                    'account_number' => $existingAccount->account_number,
                ]);
                return;
            }

            DB::beginTransaction();

            // Get director info from config
            $director = config('bellbank.director');
            $bvn = $director['bvn'] ?? null;
            $useDirectorInfo = true;

            // Check if user has KYC
            $kyc = BellKyc::where('user_id', $this->user->id)->first();
            if ($kyc && $kyc->kyc_status === 'verified' && $kyc->bvn) {
                $bvn = $kyc->bvn;
                $useDirectorInfo = false;
            }

            if (!$bvn) {
                throw new \Exception('BVN director not configured. Please set BELLBANK_DIRECTOR_BVN in .env');
            }

            // Build payload
            $idempotencyKey = Str::uuid()->toString();
            
            $payload = [
                'firstname' => $useDirectorInfo ? ($director['firstname'] ?? $this->user->username) : $this->user->username,
                'lastname' => $useDirectorInfo ? ($director['lastname'] ?? 'User') : 'User',
                'middlename' => $useDirectorInfo ? ($director['middlename'] ?? null) : null,
                'phoneNumber' => $this->user->phone ?? $director['phone'] ?? '08000000000',
                'address' => 'Nigeria', // Default address
                'bvn' => $bvn,
                'gender' => 'male',
                'dateOfBirth' => $useDirectorInfo ? ($director['date_of_birth'] ?? '1990-01-01') : '1990-01-01',
                'metadata' => [
                    'user_id' => $this->user->id,
                    'username' => $this->user->username,
                    'email' => $this->user->email,
                    'created_by' => 'auto_registration',
                    'uses_director_info' => $useDirectorInfo,
                ],
            ];

            // Create virtual account via BellBank API
            $response = $bellBankService->createIndividualClient($payload, $idempotencyKey);

            if (!isset($response['success']) || !$response['success'] || !isset($response['data'])) {
                throw new \Exception('Failed to create virtual account: ' . ($response['message'] ?? 'Unknown error'));
            }

            $data = $response['data'];
            
            // Save to database
            $account = BellAccount::create([
                'user_id' => $this->user->id,
                'external_id' => $data['id'] ?? $data['externalReference'] ?? $idempotencyKey,
                'account_number' => $data['accountNumber'] ?? null,
                'bank_code' => $data['bankCode'] ?? null,
                'bank_name' => $data['bankName'] ?? 'BellBank',
                'currency' => $data['currency'] ?? 'NGN',
                'status' => 'active',
                'metadata' => $data,
            ]);

            DB::commit();

            Log::info('BellBank virtual account created successfully', [
                'user_id' => $this->user->id,
                'account_number' => $account->account_number,
                'external_id' => $account->external_id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create BellBank virtual account', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }
}



