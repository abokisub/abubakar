<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class BellBankService
{
    protected $baseUrl;
    protected $consumerKey;
    protected $consumerSecret;
    protected $token;
    protected $tokenExpiresAt;

    public function __construct()
    {
        $this->baseUrl = config('bellbank.base_url');
        $this->consumerKey = config('bellbank.consumer_key');
        $this->consumerSecret = config('bellbank.consumer_secret');
    }

    /**
     * Generate authentication token
     */
    protected function generateToken()
    {
        // Check if we have a valid cached token
        if ($this->token && $this->tokenExpiresAt && now()->lt($this->tokenExpiresAt)) {
            return $this->token;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'consumerKey' => $this->consumerKey,
                'consumerSecret' => $this->consumerSecret,
                'validityTime' => config('bellbank.token_validity_time', 2880), // 48 hours
            ])->post("{$this->baseUrl}/v1/generate-token");

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['success']) && $data['success'] && isset($data['token'])) {
                    $this->token = $data['token'];
                    // Set expiration (subtract 5 minutes for safety)
                    $this->tokenExpiresAt = now()->addMinutes(config('bellbank.token_validity_time', 2880) - 5);
                    return $this->token;
                }
            }

            throw new Exception("Failed to generate token: " . $response->body());
        } catch (Exception $e) {
            Log::error('BellBank token generation failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get headers for API requests
     */
    protected function headers($idempotencyKey = null)
    {
        $token = $this->generateToken();
        
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$token}",
        ];

        if ($idempotencyKey) {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }

        return $headers;
    }

    /**
     * Make HTTP request with retry logic
     */
    protected function request($method, $uri, $data = [], $idempotencyKey = null)
    {
        $attempts = 0;
        $maxAttempts = config('bellbank.retry_attempts', 3);
        $baseDelay = config('bellbank.retry_delay', 1);

        do {
            $attempts++;
            
            try {
                $response = Http::withHeaders($this->headers($idempotencyKey))
                    ->timeout(config('bellbank.timeout', 30))
                    ->{$method}("{$this->baseUrl}{$uri}", $data);

                if ($response->successful()) {
                    return $response->json();
                }

                // Retry on 5xx server errors
                if ($response->serverError() && $attempts < $maxAttempts) {
                    $delay = $baseDelay * pow(2, $attempts - 1); // Exponential backoff
                    Log::warning("BellBank API server error, retrying...", [
                        'attempt' => $attempts,
                        'status' => $response->status(),
                        'uri' => $uri,
                    ]);
                    sleep($delay);
                    continue;
                }

                // Don't retry on client errors (4xx)
                $errorBody = $response->body();
                Log::error("BellBank API request failed", [
                    'method' => $method,
                    'uri' => $uri,
                    'status' => $response->status(),
                    'response' => $errorBody,
                ]);

                throw new Exception("BellBank request failed: {$errorBody}", $response->status());

            } catch (Exception $e) {
                // Retry on network/timeout errors
                if ($attempts < $maxAttempts && (
                    str_contains($e->getMessage(), 'timeout') ||
                    str_contains($e->getMessage(), 'Connection')
                )) {
                    $delay = $baseDelay * pow(2, $attempts - 1);
                    Log::warning("BellBank API network error, retrying...", [
                        'attempt' => $attempts,
                        'error' => $e->getMessage(),
                    ]);
                    sleep($delay);
                    continue;
                }

                throw $e;
            }
        } while ($attempts < $maxAttempts);

        throw new Exception("BellBank request failed after {$maxAttempts} attempts");
    }

    /**
     * Get list of supported banks
     */
    public function getBanks()
    {
        return $this->request('get', '/v1/transfer/banks');
    }

    /**
     * Name enquiry - verify account holder name
     */
    public function nameEnquiry($accountNumber, $bankCode)
    {
        return $this->request('post', '/v1/transfer/name-enquiry', [
            'accountNumber' => $accountNumber,
            'bankCode' => $bankCode,
        ]);
    }

    /**
     * Create virtual account for individual client
     */
    public function createIndividualClient(array $payload, $idempotencyKey = null)
    {
        $idempotencyKey = $idempotencyKey ?? Str::uuid()->toString();
        return $this->request('post', '/v1/account/clients/individual', $payload, $idempotencyKey);
    }

    /**
     * Create virtual account for corporate client
     */
    public function createCorporateClient(array $payload, $idempotencyKey = null)
    {
        $idempotencyKey = $idempotencyKey ?? Str::uuid()->toString();
        return $this->request('post', '/v1/account/clients/corporate', $payload, $idempotencyKey);
    }

    /**
     * Get client accounts
     */
    public function getClientAccounts($params = [])
    {
        $queryString = http_build_query($params);
        $uri = '/v1/account/clients' . ($queryString ? '?' . $queryString : '');
        return $this->request('get', $uri);
    }

    /**
     * Name enquiry (internal) - get account info by account number
     */
    public function nameEnquiryInternal($accountNumber)
    {
        return $this->request('get', "/v1/client-enquiry/{$accountNumber}");
    }

    /**
     * Get account info
     */
    public function getAccountInfo()
    {
        return $this->request('get', '/v1/account');
    }

    /**
     * Initiate bank transfer
     */
    public function initiateTransfer(array $payload, $idempotencyKey = null)
    {
        $idempotencyKey = $idempotencyKey ?? Str::uuid()->toString();
        return $this->request('post', '/v1/transfer', $payload, $idempotencyKey);
    }

    /**
     * Requery transfer by transaction ID
     */
    public function requeryTransfer($transactionId)
    {
        return $this->request('get', "/v1/transfer/tsq?transactionId={$transactionId}");
    }

    /**
     * Get transaction by reference
     */
    public function getTransactionByReference($reference)
    {
        return $this->request('get', "/v1/transactions/reference/{$reference}");
    }

    /**
     * Get all transactions
     */
    public function getAllTransactions($page = 1, $limit = 30)
    {
        $params = http_build_query(['page' => $page, 'limit' => $limit]);
        return $this->request('get', "/v1/transactions?{$params}");
    }
}



