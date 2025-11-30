<?php

return [
    /*
    |--------------------------------------------------------------------------
    | BellBank API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for BellBank (Bell MFB) API integration
    |
    */

    'base_url' => env('BELLBANK_BASE_URL', 'https://sandbox-baas-api.bellmfb.com'),
    
    'api_key' => env('BELLBANK_API_KEY'),
    
    'consumer_key' => env('BELLBANK_CONSUMER_KEY'),
    
    'consumer_secret' => env('BELLBANK_CONSUMER_SECRET'),
    
    'webhook_secret' => env('BELLBANK_WEBHOOK_SECRET'),
    
    'bvn_director' => env('BELLBANK_DIRECTOR_BVN'),
    
    'director' => [
        'bvn' => env('BELLBANK_DIRECTOR_BVN'),
        'firstname' => env('BELLBANK_DIRECTOR_FIRSTNAME'),
        'middlename' => env('BELLBANK_DIRECTOR_MIDDLENAME'),
        'lastname' => env('BELLBANK_DIRECTOR_LASTNAME'),
        'date_of_birth' => env('BELLBANK_DIRECTOR_DOB'),
        'phone' => env('BELLBANK_DIRECTOR_PHONE'),
        'email' => env('BELLBANK_DIRECTOR_EMAIL'),
    ],
    
    'timeout' => env('BELLBANK_TIMEOUT', 30),
    
    'retry_attempts' => env('BELLBANK_RETRY_ATTEMPTS', 3),
    
    'retry_delay' => env('BELLBANK_RETRY_DELAY', 1), // seconds
    
    /*
    |--------------------------------------------------------------------------
    | Token Configuration
    |--------------------------------------------------------------------------
    */
    
    'token_validity_time' => env('BELLBANK_TOKEN_VALIDITY_TIME', 2880), // minutes (48 hours max)
    
    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    */
    
    'webhook_url' => env('BELLBANK_WEBHOOK_URL', '/webhook/bellbank'),
    
    /*
    |--------------------------------------------------------------------------
    | Default Values
    |--------------------------------------------------------------------------
    */
    
    'default_currency' => 'NGN',
    
    'default_account_type' => 'individual',
];

