<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | This value determines the default payment gateway for all financial
    | operations including wallet funding, transfers, and virtual accounts.
    |
    */

    'default_gateway' => env('DEFAULT_PAYMENT_GATEWAY', 'bellbank'),

    /*
    |--------------------------------------------------------------------------
    | Available Payment Gateways
    |--------------------------------------------------------------------------
    |
    | List of available payment gateways in the system.
    | BellBank is the primary and default gateway.
    |
    */

    'gateways' => [
        'bellbank' => [
            'name' => 'BellBank',
            'enabled' => true,
            'priority' => 1, // Highest priority
        ],
        // Other gateways can be added here as optional/secondary
    ],

    /*
    |--------------------------------------------------------------------------
    | Virtual Account Settings
    |--------------------------------------------------------------------------
    */

    'virtual_account' => [
        'auto_create' => true, // Automatically create virtual account for new users
        'required_for_funding' => true, // Virtual account required for wallet funding
    ],
];



