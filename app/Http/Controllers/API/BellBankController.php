<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\BellBankService;
use App\Models\BellAccount;
use App\Models\BellTransaction;
use App\Models\BellKyc;
use App\Models\BellSetting;
use App\Models\BellBeneficiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

class BellBankController extends Controller
{
    protected $bellBankService;

    public function __construct(BellBankService $bellBankService)
    {
        $this->bellBankService = $bellBankService;
    }

    /**
     * Get list of supported banks
     */
    public function getBanks(Request $request)
    {
        try {
            $response = $this->bellBankService->getBanks();
            
            if (isset($response['success']) && $response['success']) {
                // Map API response to frontend format
                $banks = [];
                if (isset($response['data']) && is_array($response['data'])) {
                    foreach ($response['data'] as $bank) {
                        $banks[] = [
                            'code' => $bank['institutionCode'] ?? $bank['code'] ?? null,
                            'name' => $bank['institutionName'] ?? $bank['name'] ?? null,
                            'category' => $bank['category'] ?? null,
                            // Keep original format for backward compatibility
                            'institutionCode' => $bank['institutionCode'] ?? $bank['code'] ?? null,
                            'institutionName' => $bank['institutionName'] ?? $bank['name'] ?? null,
                        ];
                    }
                }
                
                // Cache bank list in settings (store both formats)
                BellSetting::setValue('last_bank_list', json_encode($banks));
                
                return response()->json([
                    'status' => 'success',
                    'data' => $banks,
                ]);
            }

            // If API fails, try to return cached banks
            $cachedBanks = BellSetting::getValue('last_bank_list');
            if ($cachedBanks) {
                $banks = json_decode($cachedBanks, true);
                if (is_array($banks) && count($banks) > 0) {
                    return response()->json([
                        'status' => 'success',
                        'data' => $banks,
                        'message' => 'Using cached bank list'
                    ]);
                }
            }

            return response()->json([
                'status' => 'fail',
                'message' => $response['message'] ?? 'Failed to fetch banks',
            ], 400);

        } catch (\Exception $e) {
            Log::error('BellBank getBanks failed', ['error' => $e->getMessage()]);
            
            // Try to return cached banks as fallback
            try {
                $cachedBanks = BellSetting::getValue('last_bank_list');
                if ($cachedBanks) {
                    $banks = json_decode($cachedBanks, true);
                    if (is_array($banks) && count($banks) > 0) {
                        return response()->json([
                            'status' => 'success',
                            'data' => $banks,
                            'message' => 'Using cached bank list (API error occurred)'
                        ]);
                    }
                }
            } catch (\Exception $cacheError) {
                Log::warning('Failed to get cached banks: ' . $cacheError->getMessage());
            }
            
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to fetch banks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * User: Get list of supported banks (with token verification)
     */
    public function userGetBanks(Request $request, $id)
    {
        // Always return a valid response, even on error
        try {
            // Verify token first
            $userId = $this->verifytoken($id);
            if (!$userId) {
                Log::warning('BellBank userGetBanks: Invalid token', ['token' => substr($id ?? '', 0, 10) . '...']);
                // Return empty array instead of 403 to prevent frontend error
                return response()->json([
                    'status' => 'success',
                    'data' => [],
                    'message' => 'Session expired. Please refresh the page.'
                ]);
            }

            // Try to get cached banks first (fastest and most reliable)
            $cachedBanks = null;
            try {
                $cachedBanks = BellSetting::getValue('last_bank_list');
                if ($cachedBanks) {
                    $banks = json_decode($cachedBanks, true);
                    if (is_array($banks) && count($banks) > 0) {
                        return response()->json([
                            'status' => 'success',
                            'data' => $banks,
                        ]);
                    }
                }
            } catch (\Exception $cacheError) {
                Log::warning('Failed to get cached banks: ' . $cacheError->getMessage());
            }

            // If no cache exists, try to fetch from API (but don't fail if it errors)
            try {
                if ($this->bellBankService && method_exists($this->bellBankService, 'getBanks')) {
                    $response = $this->bellBankService->getBanks();
                    
                    if (isset($response['success']) && $response['success'] && isset($response['data']) && is_array($response['data'])) {
                        // Map API response to frontend format
                        $banks = [];
                        foreach ($response['data'] as $bank) {
                            $banks[] = [
                                'code' => $bank['institutionCode'] ?? $bank['code'] ?? null,
                                'name' => $bank['institutionName'] ?? $bank['name'] ?? null,
                                'category' => $bank['category'] ?? null,
                                'institutionCode' => $bank['institutionCode'] ?? $bank['code'] ?? null,
                                'institutionName' => $bank['institutionName'] ?? $bank['name'] ?? null,
                            ];
                        }
                        
                        // Cache bank list
                        if (count($banks) > 0) {
                            try {
                                BellSetting::setValue('last_bank_list', json_encode($banks));
                            } catch (\Exception $saveError) {
                                Log::warning('Failed to cache banks: ' . $saveError->getMessage());
                            }
                        }
                        
                        return response()->json([
                            'status' => 'success',
                            'data' => $banks,
                        ]);
                    }
                }
            } catch (\Exception $apiError) {
                Log::warning('BellBank API call failed in userGetBanks', [
                    'error' => $apiError->getMessage(),
                    'user_id' => $userId,
                ]);
                // Continue to return empty array or cached banks
            }

            // If we get here, return empty array (no banks available)
            return response()->json([
                'status' => 'success',
                'data' => [],
                'message' => 'Banks list will be available soon'
            ]);

        } catch (\Throwable $e) {
            // Catch any error including fatal errors
            Log::error('BellBank userGetBanks critical error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Always return a valid response, never throw
            return response()->json([
                'status' => 'success',
                'data' => [],
                'message' => 'Banks temporarily unavailable'
            ]);
        }
    }

    /**
     * Admin: Get list of supported banks
     */
    public function adminGetBanks(Request $request, $id)
    {
        if (!$this->validateOrigin($request)) {
            \Log::warning('BellBank adminGetBanks origin validation failed', [
                'origin' => $request->headers->get('origin'),
                'referer' => $request->headers->get('referer'),
            ]);
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }

        $userId = $this->verifytoken($id);
        if (!$userId) {
            \Log::warning('BellBank adminGetBanks token verification failed', [
                'token' => substr($id ?? '', 0, 10) . '...'
            ]);
            return response()->json([
                'status' => 403,
                'message' => 'Invalid token'
            ], 403);
        }

        $check_user = \DB::table('user')->where(['status' => 1, 'id' => $userId])
            ->where('type', 'ADMIN')
            ->first();

        if (!$check_user) {
            \Log::warning('BellBank adminGetBanks user not admin', [
                'user_id' => $userId
            ]);
            return response()->json([
                'status' => 403,
                'message' => 'Not Authorised'
            ], 403);
        }

        try {
            $forceRefresh = $request->has('refresh') || $request->get('refresh') !== null;
            
            // If not forcing refresh, try cached banks first
            if (!$forceRefresh) {
                $cachedBanks = BellSetting::getValue('last_bank_list');
                if ($cachedBanks) {
                    $banks = json_decode($cachedBanks, true);
                    if (is_array($banks) && count($banks) > 0) {
                        return response()->json([
                            'status' => 'success',
                            'data' => $banks,
                            'message' => 'Using cached bank list'
                        ]);
                    }
                }
            }
            
            // Try to get banks from API (always on refresh, or if no cache)
            $response = $this->bellBankService->getBanks();
            
            if (isset($response['success']) && $response['success']) {
                // Map API response to frontend format
                $banks = [];
                if (isset($response['data']) && is_array($response['data'])) {
                    foreach ($response['data'] as $bank) {
                        $banks[] = [
                            'code' => $bank['institutionCode'] ?? $bank['code'] ?? null,
                            'name' => $bank['institutionName'] ?? $bank['name'] ?? null,
                            'category' => $bank['category'] ?? null,
                            // Keep original format for backward compatibility
                            'institutionCode' => $bank['institutionCode'] ?? $bank['code'] ?? null,
                            'institutionName' => $bank['institutionName'] ?? $bank['name'] ?? null,
                        ];
                    }
                }
                
                // Cache bank list in settings
                BellSetting::setValue('last_bank_list', json_encode($banks));
                
                return response()->json([
                    'status' => 'success',
                    'data' => $banks,
                ]);
            }

            // If API fails and we're not forcing refresh, try to return cached banks
            if (!$forceRefresh) {
                $cachedBanks = BellSetting::getValue('last_bank_list');
                if ($cachedBanks) {
                    $banks = json_decode($cachedBanks, true);
                    if (is_array($banks) && count($banks) > 0) {
                        return response()->json([
                            'status' => 'success',
                            'data' => $banks,
                            'message' => 'Using cached bank list (API unavailable)'
                        ]);
                    }
                }
            }

            return response()->json([
                'status' => 'fail',
                'message' => $response['message'] ?? 'Failed to fetch banks',
            ], 400);

        } catch (\Exception $e) {
            Log::error('BellBank adminGetBanks failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Try to return cached banks as fallback
            try {
                $cachedBanks = BellSetting::getValue('last_bank_list');
                if ($cachedBanks) {
                    $banks = json_decode($cachedBanks, true);
                    if (is_array($banks) && count($banks) > 0) {
                        return response()->json([
                            'status' => 'success',
                            'data' => $banks,
                            'message' => 'Using cached bank list (API error occurred)'
                        ]);
                    }
                }
            } catch (\Exception $cacheError) {
                Log::warning('Failed to get cached banks: ' . $cacheError->getMessage());
            }
            
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to fetch banks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Name enquiry - verify account holder name
     */
    public function nameEnquiry(Request $request)
    {
        $request->validate([
            'account_number' => 'required|string',
            'bank_code' => 'required|string',
        ]);

        try {
            // Map frontend field names to API field names
            $accountNumber = $request->input('account_number') ?? $request->input('accountNumber');
            $bankCode = $request->input('bank_code') ?? $request->input('bankCode');
            
            $response = $this->bellBankService->nameEnquiry(
                $accountNumber,
                $bankCode
            );

            if (isset($response['success']) && $response['success']) {
                return response()->json([
                    'status' => 'success',
                    'data' => $response['data'] ?? [],
                ]);
            }

            return response()->json([
                'status' => 'fail',
                'message' => $response['message'] ?? 'Name enquiry failed',
            ], 400);

        } catch (\Exception $e) {
            Log::error('BellBank nameEnquiry failed', [
                'error' => $e->getMessage(),
                'account_number' => substr($request->input('account_number') ?? '', 0, 4) . '****', // Partial logging
            ]);
            return response()->json([
                'status' => 'fail',
                'message' => 'Name enquiry failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: Name enquiry - verify account holder name
     */
    public function adminNameEnquiry(Request $request, $id)
    {
        if (!$this->validateOrigin($request)) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }

        $userId = $this->verifytoken($id);
        if (!$userId) {
            return response()->json([
                'status' => 403,
                'message' => 'Invalid token'
            ], 403);
        }

        $check_user = \DB::table('user')->where(['status' => 1, 'id' => $userId])
            ->where('type', 'ADMIN')
            ->first();

        if (!$check_user) {
            return response()->json([
                'status' => 403,
                'message' => 'Not Authorised'
            ], 403);
        }

        return $this->nameEnquiry($request);
    }

    /**
     * Create virtual account for individual client
     */
    public function createVirtualAccount(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'phoneNumber' => 'required|string',
            'address' => 'required|string',
            'email' => 'required|email',
        ]);

        try {
            DB::beginTransaction();

            // Use BVN director info if user doesn't have KYC
            $director = config('bellbank.director');
            $bvn = $director['bvn'] ?? null;
            $useDirectorInfo = false;

            // Check if user has KYC
            $kyc = BellKyc::where('user_id', $user->id)->first();
            if ($kyc && $kyc->kyc_status === 'verified' && $kyc->bvn) {
                $bvn = $kyc->bvn;
            } else {
                // Use director info for users without KYC
                $useDirectorInfo = true;
            }

            $idempotencyKey = Str::uuid()->toString();
            
            // Build payload - use director info if user has no KYC
            $payload = [
                'firstname' => $useDirectorInfo ? ($director['firstname'] ?? $request->firstname) : $request->firstname,
                'lastname' => $useDirectorInfo ? ($director['lastname'] ?? $request->lastname) : $request->lastname,
                'middlename' => $useDirectorInfo ? ($director['middlename'] ?? null) : ($request->middlename ?? null),
                'phoneNumber' => $request->phoneNumber,
                'address' => $request->address,
                'bvn' => $bvn,
                'gender' => $request->gender ?? 'male',
                'dateOfBirth' => $useDirectorInfo ? ($director['date_of_birth'] ?? '1990-01-01') : ($request->dateOfBirth ?? '1990-01-01'),
                'metadata' => [
                    'user_id' => $user->id,
                    'created_by' => 'system',
                    'uses_director_info' => $useDirectorInfo,
                ],
            ];

            $response = $this->bellBankService->createIndividualClient($payload, $idempotencyKey);

            if (isset($response['success']) && $response['success'] && isset($response['data'])) {
                $data = $response['data'];
                
                // Check if account already exists
                $existingAccount = BellAccount::where('external_id', $data['id'] ?? $data['externalReference'] ?? null)
                    ->first();

                if ($existingAccount) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Virtual account already exists',
                        'data' => $existingAccount,
                    ]);
                }

                $account = BellAccount::create([
                    'user_id' => $user->id,
                    'external_id' => $data['id'] ?? $data['externalReference'] ?? Str::uuid()->toString(),
                    'account_number' => $data['accountNumber'] ?? null,
                    'bank_code' => $data['bankCode'] ?? null,
                    'bank_name' => $data['bankName'] ?? 'BellBank',
                    'currency' => $data['currency'] ?? 'NGN',
                    'status' => 'active',
                    'metadata' => $data,
                ]);

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Virtual account created successfully',
                    'data' => $account,
                ], 201);
            }

            DB::rollBack();
            return response()->json([
                'status' => 'fail',
                'message' => $response['message'] ?? 'Failed to create virtual account',
            ], 400);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BellBank createVirtualAccount failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
            ]);
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to create virtual account: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Initiate bank transfer
     */
    public function initiateTransfer(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'beneficiary_bank_code' => 'required|string',
            'beneficiary_account_number' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'narration' => 'required|string',
            'reference' => 'nullable|string|unique:bell_transactions,reference',
        ]);

        try {
            DB::beginTransaction();

            // Check for duplicate using idempotency
            $idempotencyKey = $request->reference ?? Str::uuid()->toString();
            $existingTransaction = BellTransaction::where('idempotency_key', $idempotencyKey)
                ->orWhere('reference', $idempotencyKey)
                ->first();

            if ($existingTransaction) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Transaction already processed',
                    'data' => $existingTransaction,
                ]);
            }

            $payload = [
                'beneficiaryBankCode' => $request->beneficiary_bank_code,
                'beneficiaryAccountNumber' => $request->beneficiary_account_number,
                'amount' => number_format($request->amount, 2, '.', ''),
                'narration' => $request->narration,
                'reference' => $idempotencyKey,
                'senderName' => $request->sender_name ?? config('app.name'),
            ];

            $response = $this->bellBankService->initiateTransfer($payload, $idempotencyKey);

            if (isset($response['success']) && $response['success'] && isset($response['data'])) {
                $data = $response['data'];
                
                // Normalize status values (BellBank may return 'successful', we use 'success')
                $apiStatus = $data['status'] ?? 'pending';
                $normalizedStatus = $apiStatus;
                if ($apiStatus === 'successful') {
                    $normalizedStatus = 'success';
                } elseif ($apiStatus === 'failed' || $apiStatus === 'failure') {
                    $normalizedStatus = 'failed';
                }
                
                $transaction = BellTransaction::create([
                    'user_id' => $user->id,
                    'external_id' => $data['sessionId'] ?? $data['transactionId'] ?? null,
                    'type' => 'transfer',
                    'amount' => $data['amount'] ?? $request->amount,
                    'currency' => 'NGN',
                    'status' => $normalizedStatus,
                    'reference' => $data['reference'] ?? $idempotencyKey,
                    'idempotency_key' => $idempotencyKey,
                    'response' => $data,
                    'description' => $data['description'] ?? $request->narration,
                    'source_account_number' => $data['sourceAccountNumber'] ?? null,
                    'source_account_name' => $data['sourceAccountName'] ?? null,
                    'source_bank_code' => $data['sourceBankCode'] ?? null,
                    'source_bank_name' => $data['sourceBankName'] ?? null,
                    'destination_account_number' => $data['destinationAccountNumber'] ?? $request->beneficiary_account_number,
                    'destination_account_name' => $data['destinationAccountName'] ?? null,
                    'destination_bank_code' => $data['destinationBankCode'] ?? $request->beneficiary_bank_code,
                    'destination_bank_name' => $data['destinationBankName'] ?? null,
                    'charge' => $data['charge'] ?? 0,
                    'net_amount' => $data['netAmount'] ?? null,
                    'session_id' => $data['sessionId'] ?? null,
                    'transaction_type_name' => $data['transactionTypeName'] ?? 'bank_transfer',
                    'completed_at' => isset($data['completedAt']) ? date('Y-m-d H:i:s', $data['completedAt'] / 1000) : null,
                ]);

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => $response['message'] ?? 'Transfer initiated successfully',
                    'data' => $transaction,
                ], 201);
            }

            DB::rollBack();
            return response()->json([
                'status' => 'fail',
                'message' => $response['message'] ?? 'Transfer failed',
            ], 400);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BellBank initiateTransfer failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
            ]);
            return response()->json([
                'status' => 'fail',
                'message' => 'Transfer failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: Initiate bank transfer
     */
    public function adminInitiateTransfer(Request $request, $id)
    {
        if (!$this->validateOrigin($request)) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }

        $userId = $this->verifytoken($id);
        if (!$userId) {
            return response()->json([
                'status' => 403,
                'message' => 'Invalid token'
            ], 403);
        }

        $check_user = \DB::table('user')->where(['status' => 1, 'id' => $userId])
            ->where('type', 'ADMIN')
            ->first();

        if (!$check_user) {
            return response()->json([
                'status' => 403,
                'message' => 'Not Authorised'
            ], 403);
        }

        // Validate request
        $request->validate([
            'beneficiary_bank_code' => 'required|string',
            'beneficiary_account_number' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'narration' => 'required|string',
            'reference' => 'nullable|string',
            'sender_name' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Generate reference if not provided
            $idempotencyKey = $request->reference;
            if (empty($idempotencyKey)) {
                // Generate reference with business name prefix
                $businessName = config('app.name', 'KOBOPOINT');
                $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', explode(' ', $businessName)[0]));
                $idempotencyKey = $prefix . '-' . Str::random(10);
            }
            
            // Check for duplicate using idempotency
            $existingTransaction = BellTransaction::where('idempotency_key', $idempotencyKey)
                ->orWhere('reference', $idempotencyKey)
                ->first();

            if ($existingTransaction) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Transaction already processed',
                    'data' => $existingTransaction,
                ]);
            }

            $payload = [
                'beneficiaryBankCode' => $request->beneficiary_bank_code,
                'beneficiaryAccountNumber' => $request->beneficiary_account_number,
                'amount' => (float) number_format((float) $request->amount, 2, '.', ''),
                'narration' => $request->narration,
                'reference' => $idempotencyKey,
                'senderName' => $request->sender_name ?? config('app.name'),
            ];
            
            Log::info('BellBank admin transfer payload', [
                'payload' => $payload,
                'user_id' => $check_user->id,
            ]);

            $response = $this->bellBankService->initiateTransfer($payload, $idempotencyKey);

            Log::info('BellBank transfer API response', [
                'response' => $response,
                'user_id' => $check_user->id,
            ]);

            if (isset($response['success']) && $response['success'] && isset($response['data'])) {
                $data = $response['data'];
                
                // Normalize status values (BellBank may return 'successful', we use 'success')
                $apiStatus = $data['status'] ?? 'pending';
                $normalizedStatus = $apiStatus;
                if ($apiStatus === 'successful') {
                    $normalizedStatus = 'success';
                } elseif ($apiStatus === 'failed' || $apiStatus === 'failure') {
                    $normalizedStatus = 'failed';
                }
                
                try {
                    $transaction = BellTransaction::create([
                        'user_id' => $check_user->id,
                        'external_id' => $data['sessionId'] ?? $data['transactionId'] ?? null,
                        'type' => 'transfer',
                        'amount' => $data['amount'] ?? $request->amount,
                        'currency' => 'NGN',
                        'status' => $normalizedStatus,
                        'reference' => $data['reference'] ?? $idempotencyKey,
                        'idempotency_key' => $idempotencyKey,
                        'response' => $data,
                        'description' => $data['description'] ?? $request->narration,
                        'source_account_number' => $data['sourceAccountNumber'] ?? null,
                        'source_account_name' => $data['sourceAccountName'] ?? null,
                        'source_bank_code' => $data['sourceBankCode'] ?? null,
                        'source_bank_name' => $data['sourceBankName'] ?? null,
                        'destination_account_number' => $data['destinationAccountNumber'] ?? $request->beneficiary_account_number,
                        'destination_account_name' => $data['destinationAccountName'] ?? null,
                        'destination_bank_code' => $data['destinationBankCode'] ?? $request->beneficiary_bank_code,
                        'destination_bank_name' => $data['destinationBankName'] ?? null,
                        'charge' => $data['charge'] ?? 0,
                        'net_amount' => $data['netAmount'] ?? null,
                        'session_id' => $data['sessionId'] ?? null,
                        'transaction_type_name' => $data['transactionTypeName'] ?? 'bank_transfer',
                        'completed_at' => isset($data['completedAt']) ? date('Y-m-d H:i:s', $data['completedAt'] / 1000) : null,
                    ]);

                    DB::commit();

                    return response()->json([
                        'status' => 'success',
                        'message' => $response['message'] ?? 'Transfer initiated successfully',
                        'data' => $transaction,
                    ], 201);
                } catch (\Exception $dbError) {
                    DB::rollBack();
                    Log::error('BellBank transfer database save failed', [
                        'error' => $dbError->getMessage(),
                        'trace' => $dbError->getTraceAsString(),
                        'user_id' => $check_user->id,
                    ]);
                    return response()->json([
                        'status' => 'fail',
                        'message' => 'Failed to save transaction: ' . $dbError->getMessage(),
                    ], 500);
                }
            }

            DB::rollBack();
            
            // Extract user-friendly error message
            $errorMessage = 'Transfer failed. Please try again.';
            if (isset($response['message'])) {
                $errorMessage = $response['message'];
                
                // If message contains JSON, try to parse it
                if (strpos($errorMessage, '{') !== false && strpos($errorMessage, '}') !== false) {
                    try {
                        preg_match('/\{[^}]+\}/', $errorMessage, $matches);
                        if (!empty($matches[0])) {
                            $errorData = json_decode($matches[0], true);
                            if (isset($errorData['message'])) {
                                $errorMessage = $errorData['message'];
                            }
                        }
                    } catch (\Exception $e) {
                        // If parsing fails, use original message
                    }
                }
            } elseif (isset($response['error'])) {
                $errorMessage = $response['error'];
            }
            
            Log::error('BellBank transfer API failed', [
                'response' => $response,
                'user_id' => $check_user->id,
                'error_message' => $errorMessage,
            ]);
            
            return response()->json([
                'status' => 'fail',
                'message' => $errorMessage,
            ], 400);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'fail',
                'message' => 'Validation failed: ' . implode(', ', $e->errors()),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BellBank adminInitiateTransfer failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $check_user->id ?? null,
            ]);
            
            // Return user-friendly error message
            $errorMessage = 'Transfer failed. Please try again.';
            if (strpos($e->getMessage(), 'Insufficient balance') !== false) {
                $errorMessage = 'Insufficient balance in your BellBank account. Please fund your account and try again.';
            } elseif (strpos($e->getMessage(), 'Invalid account') !== false) {
                $errorMessage = 'The beneficiary account number is invalid. Please verify and try again.';
            } elseif (strpos($e->getMessage(), 'Invalid bank') !== false) {
                $errorMessage = 'The bank code is invalid. Please select a valid bank.';
            }
            
            return response()->json([
                'status' => 'fail',
                'message' => $errorMessage,
            ], 500);
        }
    }

    /**
     * Get transaction status by reference
     */
    public function getTransactionStatus(Request $request, $reference)
    {
        try {
            $transaction = BellTransaction::where('reference', $reference)->first();
            
            if ($transaction) {
                return response()->json([
                    'status' => 'success',
                    'data' => $transaction,
                ]);
            }

            // Query BellBank API if not found locally
            $response = $this->bellBankService->getTransactionByReference($reference);
            
            if (isset($response['success']) && $response['success']) {
                return response()->json([
                    'status' => 'success',
                    'data' => $response['data'] ?? [],
                ]);
            }

            return response()->json([
                'status' => 'fail',
                'message' => 'Transaction not found',
            ], 404);

        } catch (\Exception $e) {
            Log::error('BellBank getTransactionStatus failed', [
                'error' => $e->getMessage(),
                'reference' => $reference,
            ]);
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to get transaction status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * User: Get transfer receipt details (with token verification)
     */
    public function getUserTransferReceipt(Request $request, $reference, $id)
    {
        $userId = $this->verifytoken($id);
        if (!$userId) {
            return response()->json([
                'status' => 403,
                'message' => 'Invalid token'
            ], 403);
        }

        try {
            $transaction = BellTransaction::where('reference', $reference)
                ->where('user_id', $userId)
                ->where('type', 'transfer')
                ->first();
            
            if (!$transaction) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Transaction not found'
                ], 404);
            }

            // Get user details for receipt
            $user = \DB::table('user')->where('id', $userId)->first();
            
            // Map status to plan_status format (0=pending, 1=success, 2=failed)
            $planStatus = 0;
            if (in_array($transaction->status, ['success', 'successful'])) {
                $planStatus = 1;
            } elseif (in_array($transaction->status, ['failed', 'failure'])) {
                $planStatus = 2;
            }

            // Calculate old balance (new balance - amount - charge)
            $oldBalance = ($user->bal ?? 0) - ($transaction->amount + ($transaction->charge ?? 0));

            return response()->json([
                'status' => 'success',
                'data' => [
                    'transid' => $transaction->reference,
                    'reference' => $transaction->reference,
                    'amount' => $transaction->amount,
                    'charge' => $transaction->charge ?? 0,
                    'oldbal' => $oldBalance,
                    'newbal' => $user->bal ?? 0,
                    'plan_status' => $planStatus,
                    'status' => $transaction->status,
                    'date' => $transaction->created_at,
                    'destination_account_number' => $transaction->destination_account_number,
                    'destination_account_name' => $transaction->destination_account_name,
                    'destination_bank_name' => $transaction->destination_bank_name,
                    'destination_bank_code' => $transaction->destination_bank_code,
                    'description' => $transaction->description,
                    'narration' => $transaction->description,
                    'type' => 'BellBank Transfer',
                    'wallet_type' => 'User Wallet',
                    'completed_at' => $transaction->completed_at,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('BellBank getUserTransferReceipt failed', [
                'error' => $e->getMessage(),
                'reference' => $reference,
                'user_id' => $userId,
            ]);
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to get transfer receipt: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: Get all virtual accounts with pagination
     */
    public function adminGetAccounts(Request $request, $id)
    {
        if (!$this->validateOrigin($request)) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }

        $userId = $this->verifytoken($id);
        if (!$userId) {
            return response()->json([
                'status' => 403,
                'message' => 'Invalid token'
            ], 403);
        }

        $check_user = \DB::table('user')->where(['status' => 1, 'id' => $userId])
            ->where('type', 'ADMIN')
            ->first();

        if (!$check_user) {
            return response()->json([
                'status' => 403,
                'message' => 'Not Authorised'
            ], 403);
        }

        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');
            $status = $request->get('status', 'ALL');
            $role = $request->get('role', 'ALL');

            // Start with base query
            $query = BellAccount::query();

            // Eager load user relationship
            $query->with('user');

            // Filter by user status (ALL, 0=Unverified, 1=Active, 2=Banned, 3=Deactivated)
            if ($status !== 'ALL') {
                $query->whereHas('user', function($userQuery) use ($status) {
                    $userQuery->where('status', $status);
                });
            }

            // Filter by user role/type
            if ($role !== 'ALL') {
                $query->whereHas('user', function($userQuery) use ($role) {
                    $userQuery->where('type', $role);
                });
            }

            // Search functionality
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('account_number', 'like', "%{$search}%")
                      ->orWhere('bank_name', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('username', 'like', "%{$search}%")
                                    ->orWhere('name', 'like', "%{$search}%");
                      });
                });
            }

            // Get total count before pagination for debugging
            $totalCount = $query->count();
            Log::info('BellBank adminGetAccounts query', [
                'total_count' => $totalCount,
                'page' => $page,
                'per_page' => $perPage,
                'status' => $status,
                'role' => $role,
                'search' => $search,
            ]);

            $accounts = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Log the results
            Log::info('BellBank adminGetAccounts results', [
                'items_count' => count($accounts->items()),
                'total' => $accounts->total(),
                'current_page' => $accounts->currentPage(),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'data' => $accounts->items(),
                    'total' => $accounts->total(),
                    'per_page' => $accounts->perPage(),
                    'current_page' => $accounts->currentPage(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('BellBank adminGetAccounts failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to fetch accounts: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: Get all transactions with pagination
     */
    public function adminGetTransactions(Request $request, $id)
    {
        if (!$this->validateOrigin($request)) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }

        $userId = $this->verifytoken($id);
        if (!$userId) {
            return response()->json([
                'status' => 403,
                'message' => 'Invalid token'
            ], 403);
        }

        $check_user = \DB::table('user')->where(['status' => 1, 'id' => $userId])
            ->where('type', 'ADMIN')
            ->first();

        if (!$check_user) {
            return response()->json([
                'status' => 403,
                'message' => 'Not Authorised'
            ], 403);
        }

        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');
            $status = $request->get('status', '');

            $query = BellTransaction::with('user');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('reference', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('username', 'like', "%{$search}%")
                                    ->orWhere('name', 'like', "%{$search}%");
                      });
                });
            }

            if ($status && $status !== 'ALL') {
                $query->where('status', $status);
            }

            $transactions = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'data' => $transactions->items(),
                    'total' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                    'current_page' => $transactions->currentPage(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('BellBank adminGetTransactions failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to fetch transactions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: Get all transfers with pagination
     */
    public function adminGetTransfers(Request $request, $id)
    {
        if (!$this->validateOrigin($request)) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }

        $userId = $this->verifytoken($id);
        if (!$userId) {
            return response()->json([
                'status' => 403,
                'message' => 'Invalid token'
            ], 403);
        }

        $check_user = \DB::table('user')->where(['status' => 1, 'id' => $userId])
            ->where('type', 'ADMIN')
            ->first();

        if (!$check_user) {
            return response()->json([
                'status' => 403,
                'message' => 'Not Authorised'
            ], 403);
        }

        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');

            $query = BellTransaction::with('user')
                ->where('type', 'transfer');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('reference', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('destination_account_number', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('username', 'like', "%{$search}%")
                                    ->orWhere('name', 'like', "%{$search}%");
                      });
                });
            }

            $transfers = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'data' => $transfers->items(),
                    'total' => $transfers->total(),
                    'per_page' => $transfers->perPage(),
                    'current_page' => $transfers->currentPage(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('BellBank adminGetTransfers failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to fetch transfers: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: Refresh transaction status from BellBank API
     */
    public function adminRefreshTransactionStatus(Request $request, $id, $reference)
    {
        if (!$this->validateOrigin($request)) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }

        $userId = $this->verifytoken($id);
        if (!$userId) {
            return response()->json([
                'status' => 403,
                'message' => 'Invalid token'
            ], 403);
        }

        $check_user = \DB::table('user')->where(['status' => 1, 'id' => $userId])
            ->where('type', 'ADMIN')
            ->first();

        if (!$check_user) {
            return response()->json([
                'status' => 403,
                'message' => 'Not Authorised'
            ], 403);
        }

        try {
            // Find transaction by reference
            $transaction = BellTransaction::where('reference', $reference)->first();

            if (!$transaction) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Transaction not found'
                ], 404);
            }

            // Query BellBank API for latest status
            $response = $this->bellBankService->getTransactionByReference($reference);

            if (isset($response['success']) && $response['success'] && isset($response['data'])) {
                $data = $response['data'];
                
                // Normalize status values (BellBank may return 'successful', we use 'success')
                $apiStatus = $data['status'] ?? $transaction->status;
                $normalizedStatus = $apiStatus;
                if ($apiStatus === 'successful') {
                    $normalizedStatus = 'success';
                } elseif ($apiStatus === 'failed' || $apiStatus === 'failure') {
                    $normalizedStatus = 'failed';
                }

                // Update transaction with latest status from API
                $transaction->update([
                    'status' => $normalizedStatus,
                    'response' => $data,
                    'completed_at' => isset($data['completedAt']) 
                        ? date('Y-m-d H:i:s', $data['completedAt'] / 1000) 
                        : ($transaction->completed_at ?? now()),
                ]);

                Log::info('BellBank transaction status refreshed', [
                    'reference' => $reference,
                    'old_status' => $transaction->getOriginal('status'),
                    'new_status' => $transaction->status,
                    'user_id' => $check_user->id,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Transaction status updated',
                    'data' => $transaction->fresh(),
                ]);
            }

            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to fetch status from BellBank API',
            ], 400);

        } catch (\Exception $e) {
            Log::error('BellBank adminRefreshTransactionStatus failed', [
                'error' => $e->getMessage(),
                'reference' => $reference,
                'user_id' => $check_user->id,
            ]);
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to refresh status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: Get all KYC records with pagination
     */
    public function adminGetKYC(Request $request, $id)
    {
        if (!$this->validateOrigin($request)) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }

        $userId = $this->verifytoken($id);
        if (!$userId) {
            return response()->json([
                'status' => 403,
                'message' => 'Invalid token'
            ], 403);
        }

        $check_user = \DB::table('user')->where(['status' => 1, 'id' => $userId])
            ->where('type', 'ADMIN')
            ->first();

        if (!$check_user) {
            return response()->json([
                'status' => 403,
                'message' => 'Not Authorised'
            ], 403);
        }

        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');

            $query = BellKyc::with('user');

            if ($search) {
                $query->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('username', 'like', "%{$search}%")
                              ->orWhere('name', 'like', "%{$search}%");
                });
            }

            $kycRecords = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'data' => $kycRecords->items(),
                    'total' => $kycRecords->total(),
                    'per_page' => $kycRecords->perPage(),
                    'current_page' => $kycRecords->currentPage(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('BellBank adminGetKYC failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to fetch KYC records: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: Get all webhooks with pagination
     */
    public function adminGetWebhooks(Request $request, $id)
    {
        if (!$this->validateOrigin($request)) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }

        $userId = $this->verifytoken($id);
        if (!$userId) {
            return response()->json([
                'status' => 403,
                'message' => 'Invalid token'
            ], 403);
        }

        $check_user = \DB::table('user')->where(['status' => 1, 'id' => $userId])
            ->where('type', 'ADMIN')
            ->first();

        if (!$check_user) {
            return response()->json([
                'status' => 403,
                'message' => 'Not Authorised'
            ], 403);
        }

        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');

            $query = \App\Models\BellWebhook::query();

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('event', 'like', "%{$search}%")
                      ->orWhere('payload', 'like', "%{$search}%");
                });
            }

            $webhooks = $query->orderBy('received_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Transform webhook data for frontend
            $transformedWebhooks = $webhooks->map(function($webhook) {
                $payload = is_array($webhook->payload) ? $webhook->payload : json_decode($webhook->payload, true);
                
                // Extract amount based on BellBank API structure
                $amount = $payload['amountReceived'] 
                    ?? $payload['netAmount'] 
                    ?? $payload['amount'] 
                    ?? $payload['transactionAmount'] 
                    ?? 0;
                
                // Extract reference from various possible fields
                $reference = $payload['reference'] 
                    ?? $payload['transactionReference'] 
                    ?? $payload['externalReference']
                    ?? null;
                
                return [
                    'id' => $webhook->id,
                    'event_type' => $webhook->event,
                    'reference' => $reference,
                    'amount' => (float) $amount,
                    'net_amount' => $payload['netAmount'] ?? null,
                    'transaction_fee' => $payload['transactionFee'] ?? null,
                    'status' => $webhook->processed ? 'processed' : ($webhook->error ? 'failed' : 'pending'),
                    'payload_status' => $payload['status'] ?? null, // Status from BellBank API
                    'error' => $webhook->error,
                    'created_at' => $webhook->received_at ? $webhook->received_at->toDateTimeString() : $webhook->created_at,
                    'payload' => $payload, // Include full payload for details view
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'data' => $transformedWebhooks->toArray(),
                    'total' => $webhooks->total(),
                    'per_page' => $webhooks->perPage(),
                    'current_page' => $webhooks->currentPage(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('BellBank adminGetWebhooks failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to fetch webhooks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: Get BellBank settings
     */
    public function adminGetSettings(Request $request, $id)
    {
        if (!$this->validateOrigin($request)) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }

        $userId = $this->verifytoken($id);
        if (!$userId) {
            return response()->json([
                'status' => 403,
                'message' => 'Invalid token'
            ], 403);
        }

        $check_user = \DB::table('user')->where(['status' => 1, 'id' => $userId])
            ->where('type', 'ADMIN')
            ->first();

        if (!$check_user) {
            return response()->json([
                'status' => 403,
                'message' => 'Not Authorised'
            ], 403);
        }

        try {
            $settings = [
                'base_url' => config('bellbank.base_url', ''),
                'consumer_key' => config('bellbank.consumer_key', ''),
                'consumer_secret' => config('bellbank.consumer_secret', ''),
                'webhook_secret' => config('bellbank.webhook_secret', ''),
                'timeout' => config('bellbank.timeout', '30'),
                'retry_attempts' => config('bellbank.retry_attempts', '3'),
                'retry_delay' => config('bellbank.retry_delay', '1'),
                'transfer_charge' => BellSetting::getValue('transfer_charge', '0'),
                'virtual_account_charge' => BellSetting::getValue('virtual_account_charge', '0'),
            ];

            return response()->json([
                'status' => 'success',
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            Log::error('BellBank adminGetSettings failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to fetch settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: Update BellBank settings
     */
    public function adminUpdateSettings(Request $request, $id)
    {
        if (!$this->validateOrigin($request)) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Origin not allowed'
            ], 403);
        }

        $userId = $this->verifytoken($id);
        if (!$userId) {
            return response()->json([
                'status' => 403,
                'message' => 'Invalid token'
            ], 403);
        }

        $check_user = \DB::table('user')->where(['status' => 1, 'id' => $userId])
            ->where('type', 'ADMIN')
            ->first();

        if (!$check_user) {
            return response()->json([
                'status' => 403,
                'message' => 'Not Authorised'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'base_url' => 'nullable|string|url',
                'consumer_key' => 'nullable|string',
                'consumer_secret' => 'nullable|string',
                'webhook_secret' => 'nullable|string',
                'timeout' => 'nullable|integer|min:1',
                'retry_attempts' => 'nullable|integer|min:0',
                'retry_delay' => 'nullable|integer|min:0',
                'transfer_charge' => 'nullable|numeric|min:0',
                'virtual_account_charge' => 'nullable|numeric|min:0',
            ]);

            // Save charges to BellSetting
            if (isset($validated['transfer_charge'])) {
                BellSetting::setValue('transfer_charge', $validated['transfer_charge']);
            }
            if (isset($validated['virtual_account_charge'])) {
                BellSetting::setValue('virtual_account_charge', $validated['virtual_account_charge']);
            }

            // Note: API credentials are typically in .env, but we save charges to database
            return response()->json([
                'status' => 'success',
                'message' => 'Settings updated successfully',
                'data' => $validated
            ]);
        } catch (\Exception $e) {
            Log::error('BellBank adminUpdateSettings failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to update settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's beneficiaries
     */
    public function getBeneficiaries(Request $request, $id)
    {
        try {
            $userId = $this->verifytoken($id);
            if (!$userId) {
                Log::warning('BellBank getBeneficiaries: Invalid token', ['token' => substr($id ?? '', 0, 10) . '...']);
                return response()->json([
                    'status' => 403,
                    'message' => 'Invalid token'
                ], 403);
            }

            // Check if table exists, if not return empty array
            try {
                $beneficiaries = BellBeneficiary::where('user_id', $userId)
                    ->orderBy('last_used_at', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();

                return response()->json([
                    'status' => 'success',
                    'data' => $beneficiaries
                ]);
            } catch (\Illuminate\Database\QueryException $dbError) {
                // Table might not exist yet - return empty array
                if (str_contains($dbError->getMessage(), "doesn't exist") || str_contains($dbError->getMessage(), 'Base table')) {
                    Log::info('BellBank beneficiaries table does not exist yet, returning empty array');
                    return response()->json([
                        'status' => 'success',
                        'data' => []
                    ]);
                }
                throw $dbError; // Re-throw if it's a different database error
            }
        } catch (\Exception $e) {
            Log::error('BellBank getBeneficiaries failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'token' => substr($id ?? '', 0, 10) . '...',
            ]);
            // Return empty array instead of error to prevent frontend crashes
            return response()->json([
                'status' => 'success',
                'data' => [],
                'message' => 'Beneficiaries feature not available yet'
            ]);
        }
    }

    /**
     * Save beneficiary (after successful transfer or manual save)
     */
    public function saveBeneficiary(Request $request, $id)
    {
        $userId = $this->verifytoken($id);
        if (!$userId) {
            return response()->json([
                'status' => 403,
                'message' => 'Invalid token'
            ], 403);
        }

        $request->validate([
            'account_number' => 'required|string',
            'account_name' => 'required|string',
            'bank_code' => 'required|string',
            'bank_name' => 'nullable|string',
        ]);

        try {
            // Check if beneficiary already exists
            $existing = BellBeneficiary::where('user_id', $userId)
                ->where('account_number', $request->account_number)
                ->where('bank_code', $request->bank_code)
                ->first();

            if ($existing) {
                // Update last used
                $existing->update([
                    'account_name' => $request->account_name,
                    'bank_name' => $request->bank_name ?? $existing->bank_name,
                    'last_used_at' => now(),
                    'transfer_count' => $existing->transfer_count + 1,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Beneficiary updated',
                    'data' => $existing->fresh()
                ]);
            }

            // Create new beneficiary
            $beneficiary = BellBeneficiary::create([
                'user_id' => $userId,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'bank_code' => $request->bank_code,
                'bank_name' => $request->bank_name,
                'last_used_at' => now(),
                'transfer_count' => 1,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Beneficiary saved',
                'data' => $beneficiary
            ]);
        } catch (\Exception $e) {
            Log::error('BellBank saveBeneficiary failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to save beneficiary: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete beneficiary
     */
    public function deleteBeneficiary(Request $request, $beneficiaryId, $id)
    {
        $userId = $this->verifytoken($id);
        if (!$userId) {
            return response()->json([
                'status' => 403,
                'message' => 'Invalid token'
            ], 403);
        }

        try {
            $beneficiary = BellBeneficiary::where('id', $beneficiaryId)
                ->where('user_id', $userId)
                ->first();

            if (!$beneficiary) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Beneficiary not found'
                ], 404);
            }

            $beneficiary->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Beneficiary deleted'
            ]);
        } catch (\Exception $e) {
            Log::error('BellBank deleteBeneficiary failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to delete beneficiary: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle favorite status
     */
    public function toggleFavoriteBeneficiary(Request $request, $beneficiaryId, $id)
    {
        $userId = $this->verifytoken($id);
        if (!$userId) {
            return response()->json([
                'status' => 403,
                'message' => 'Invalid token'
            ], 403);
        }

        try {
            $beneficiary = BellBeneficiary::where('id', $beneficiaryId)
                ->where('user_id', $userId)
                ->first();

            if (!$beneficiary) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Beneficiary not found'
                ], 404);
            }

            $beneficiary->update([
                'is_favorite' => !$beneficiary->is_favorite
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $beneficiary->is_favorite ? 'Added to favorites' : 'Removed from favorites',
                'data' => $beneficiary->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('BellBank toggleFavoriteBeneficiary failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to update beneficiary: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * User name enquiry endpoint (with token verification)
     */
    public function userNameEnquiry(Request $request, $id)
    {
        $userId = $this->verifytoken($id);
        if (!$userId) {
            return response()->json([
                'status' => 403,
                'message' => 'Invalid token'
            ], 403);
        }

        return $this->nameEnquiry($request);
    }

    /**
     * User: Initiate bank transfer (with token verification)
     */
    public function userInitiateTransfer(Request $request, $id)
    {
        $userId = $this->verifytoken($id);
        if (!$userId) {
            return response()->json([
                'status' => 403,
                'message' => 'Invalid token'
            ], 403);
        }

        // Get user from database
        $user = \DB::table('user')->where('id', $userId)->where('status', 1)->first();
        if (!$user) {
            return response()->json([
                'status' => 403,
                'message' => 'User not found or inactive'
            ], 403);
        }

        // Validate request
        $request->validate([
            'beneficiary_bank_code' => 'required|string',
            'beneficiary_account_number' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'narration' => 'required|string',
            'reference' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Generate reference if not provided
            $idempotencyKey = $request->reference;
            if (empty($idempotencyKey)) {
                // Generate reference with business name prefix
                $businessName = config('app.name', 'KOBOPOINT');
                $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', explode(' ', $businessName)[0]));
                $idempotencyKey = $prefix . '-' . Str::random(10);
            }
            
            // Check for duplicate using idempotency
            $existingTransaction = BellTransaction::where('idempotency_key', $idempotencyKey)
                ->orWhere('reference', $idempotencyKey)
                ->first();

            if ($existingTransaction) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Transaction already processed',
                    'data' => $existingTransaction,
                ]);
            }

            $payload = [
                'beneficiaryBankCode' => $request->beneficiary_bank_code,
                'beneficiaryAccountNumber' => $request->beneficiary_account_number,
                'amount' => (float) number_format((float) $request->amount, 2, '.', ''),
                'narration' => $request->narration,
                'reference' => $idempotencyKey,
                'senderName' => $request->sender_name ?? config('app.name'),
            ];

            $response = $this->bellBankService->initiateTransfer($payload, $idempotencyKey);

            if (isset($response['success']) && $response['success'] && isset($response['data'])) {
                $data = $response['data'];
                
                // Normalize status values (BellBank may return 'successful', we use 'success')
                $apiStatus = $data['status'] ?? 'pending';
                $normalizedStatus = $apiStatus;
                if ($apiStatus === 'successful') {
                    $normalizedStatus = 'success';
                } elseif ($apiStatus === 'failed' || $apiStatus === 'failure') {
                    $normalizedStatus = 'failed';
                }
                
                $transaction = BellTransaction::create([
                    'user_id' => $user->id,
                    'external_id' => $data['sessionId'] ?? $data['transactionId'] ?? null,
                    'type' => 'transfer',
                    'amount' => $data['amount'] ?? $request->amount,
                    'currency' => 'NGN',
                    'status' => $normalizedStatus,
                    'reference' => $data['reference'] ?? $idempotencyKey,
                    'idempotency_key' => $idempotencyKey,
                    'response' => $data,
                    'description' => $data['description'] ?? $request->narration,
                    'source_account_number' => $data['sourceAccountNumber'] ?? null,
                    'source_account_name' => $data['sourceAccountName'] ?? null,
                    'source_bank_code' => $data['sourceBankCode'] ?? null,
                    'source_bank_name' => $data['sourceBankName'] ?? null,
                    'destination_account_number' => $data['destinationAccountNumber'] ?? $request->beneficiary_account_number,
                    'destination_account_name' => $data['destinationAccountName'] ?? null,
                    'destination_bank_code' => $data['destinationBankCode'] ?? $request->beneficiary_bank_code,
                    'destination_bank_name' => $data['destinationBankName'] ?? null,
                    'charge' => $data['charge'] ?? 0,
                    'net_amount' => $data['netAmount'] ?? null,
                    'session_id' => $data['sessionId'] ?? null,
                    'transaction_type_name' => $data['transactionTypeName'] ?? 'bank_transfer',
                    'completed_at' => isset($data['completedAt']) ? date('Y-m-d H:i:s', $data['completedAt'] / 1000) : null,
                ]);

                // Only deduct balance and create message entry if transfer is immediately successful
                // If pending, the webhook will handle it when status changes to success
                if ($normalizedStatus === 'success') {
                    // Get user's current balance before transfer
                    $oldBalance = (float) $user->bal;
                    $charge = $data['charge'] ?? 0;
                    $totalDeducted = $transaction->amount + $charge;
                    $newBalance = $oldBalance - $totalDeducted;
                    
                    // Update user balance
                    DB::table('user')->where('id', $user->id)->update(['bal' => $newBalance]);
                    
                    // Create message entry for transaction history
                    try {
                        DB::table('message')->insert([
                            'username' => $user->username,
                            'amount' => $transaction->amount,
                            'message' => 'Bank Transfer to ' . ($data['destinationAccountName'] ?? $request->beneficiary_account_number) . ' - ' . ($data['destinationBankName'] ?? ''),
                            'oldbal' => $oldBalance,
                            'newbal' => $newBalance,
                            'adex_date' => now()->format('Y-m-d H:i:s'),
                            'plan_status' => 1, // success
                            'transid' => $transaction->reference,
                            'role' => 'debit'
                        ]);
                    } catch (\Exception $messageError) {
                        Log::warning('Failed to create message entry for transfer', [
                            'error' => $messageError->getMessage(),
                            'user_id' => $user->id,
                            'reference' => $transaction->reference,
                        ]);
                    }
                }

                // Automatically save beneficiary after successful transfer
                try {
                    $beneficiaryAccountName = $data['destinationAccountName'] ?? null;
                    $beneficiaryBankName = $data['destinationBankName'] ?? null;
                    
                    // If bank name is not in response, try to get it from cached banks
                    if (!$beneficiaryBankName) {
                        $cachedBanks = BellSetting::getValue('last_bank_list');
                        if ($cachedBanks) {
                            $banks = json_decode($cachedBanks, true);
                            if (is_array($banks)) {
                                $bank = collect($banks)->first(function($b) use ($request) {
                                    return ($b['code'] ?? $b['institutionCode']) === $request->beneficiary_bank_code;
                                });
                                if ($bank) {
                                    $beneficiaryBankName = $bank['name'] ?? $bank['institutionName'] ?? null;
                                }
                            }
                        }
                    }

                    // Check if beneficiary already exists
                    $existingBeneficiary = BellBeneficiary::where('user_id', $user->id)
                        ->where('account_number', $request->beneficiary_account_number)
                        ->where('bank_code', $request->beneficiary_bank_code)
                        ->first();

                    if ($existingBeneficiary) {
                        // Update existing beneficiary
                        $existingBeneficiary->update([
                            'account_name' => $beneficiaryAccountName ?? $existingBeneficiary->account_name,
                            'bank_name' => $beneficiaryBankName ?? $existingBeneficiary->bank_name,
                            'last_used_at' => now(),
                            'transfer_count' => $existingBeneficiary->transfer_count + 1,
                        ]);
                    } else {
                        // Create new beneficiary
                        BellBeneficiary::create([
                            'user_id' => $user->id,
                            'account_number' => $request->beneficiary_account_number,
                            'account_name' => $beneficiaryAccountName ?? 'Unknown',
                            'bank_code' => $request->beneficiary_bank_code,
                            'bank_name' => $beneficiaryBankName,
                            'last_used_at' => now(),
                            'transfer_count' => 1,
                        ]);
                    }
                } catch (\Exception $beneficiaryError) {
                    // Log but don't fail the transfer if beneficiary save fails
                    Log::warning('Failed to save beneficiary after transfer', [
                        'error' => $beneficiaryError->getMessage(),
                        'user_id' => $user->id,
                    ]);
                }

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => $response['message'] ?? 'Transfer initiated successfully',
                    'data' => $transaction,
                ], 201);
            }

            DB::rollBack();
            return response()->json([
                'status' => 'fail',
                'message' => $response['message'] ?? 'Transfer failed',
            ], 400);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BellBank userInitiateTransfer failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
            ]);
            return response()->json([
                'status' => 'fail',
                'message' => 'Transfer failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify token helper (uses parent method which checks adex_key)
     */
    public function verifytoken($token)
    {
        // Use parent's verifytoken method which checks adex_key
        return parent::verifytoken($token);
    }

    /**
     * Validate origin (same as AdminController)
     */
    protected function validateOrigin(Request $request)
    {
        $allowedOrigins = array_filter(array_map('trim', explode(',', config('adex.app_key', ''))));
        $origin = $request->headers->get('origin');
        $referer = $request->headers->get('referer');
        $host = $request->getHost();
        $fullUrl = $request->getSchemeAndHttpHost();

        $originNormalized = rtrim($origin ?: '', '/');
        $isSameOrigin = false;
        $refererMatches = false;

        if (!$origin && $referer) {
            $refererHost = parse_url($referer, PHP_URL_HOST);
            if ($refererHost === $host) {
                $isSameOrigin = true;
            }
        }

        if ($referer) {
            $refererUrl = parse_url($referer, PHP_URL_SCHEME) . '://' . parse_url($referer, PHP_URL_HOST);
            $refererNormalized = rtrim($refererUrl ?: '', '/');
            $refererMatches = in_array($refererNormalized, $allowedOrigins);
        }

        return in_array($originNormalized, $allowedOrigins)
            || $refererMatches
            || $isSameOrigin
            || config('adex.device_key') === $request->header('Authorization')
            || in_array($fullUrl, $allowedOrigins);
    }
}

