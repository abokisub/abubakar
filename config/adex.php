<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Adex Application Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the Adex application.
    | It maps environment variables to configuration keys to ensure
    | compatibility with 'php artisan config:cache'.
    |
    */

    'app_key' => env('ADEX_APP_KEY'),
    'device_key' => env('ADEX_DEVICE_KEY'),
    'error_500' => env('ERROR_500', '/500'), // Default to /500 if not set
];
