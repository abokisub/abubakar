<?php

/**
 * URL Helper Functions
 * 
 * Provides environment-aware URL generation and redirection
 */

if (!function_exists('getAppUrl')) {
    /**
     * Get application URL with auto-detection
     * Falls back to request()->getSchemeAndHttpHost() if not set in .env
     * 
     * @return string
     */
    function getAppUrl()
    {
        $url = env('APP_URL');
        
        // Auto-detect if not set - use Laravel's request helper
        if (!$url) {
            try {
                $request = request();
                if ($request) {
                    $url = $request->getSchemeAndHttpHost();
                } else {
                    // Fallback if request not available
                    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    $url = $scheme . '://' . $host;
                }
            } catch (\Exception $e) {
                // Final fallback
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $url = $scheme . '://' . $host;
            }
        }
        
        // Remove trailing slash
        return rtrim($url, '/');
    }
}

if (!function_exists('getFrontendUrl')) {
    /**
     * Get frontend URL with auto-detection
     * 
     * @return string
     */
    function getFrontendUrl()
    {
        $url = env('FRONTEND_URL');
        
        // Auto-detect if not set (same as APP_URL for cPanel deployment)
        if (!$url) {
            $url = getAppUrl();
        }
        
        return rtrim($url, '/');
    }
}

if (!function_exists('getErrorUrl')) {
    /**
     * Get error page URL
     * 
     * @return string
     */
    function getErrorUrl()
    {
        $url = env('ERROR_500', getFrontendUrl() . '/500');
        return rtrim($url, '/');
    }
}

if (!function_exists('getApiUrl')) {
    /**
     * Get API URL
     * 
     * @return string
     */
    function getApiUrl()
    {
        $url = env('API_URL', getAppUrl() . '/api');
        return rtrim($url, '/');
    }
}

if (!function_exists('isOriginAllowed')) {
    /**
     * Check if request origin is allowed (ADEX_APP_KEY validation with auto-detection)
     * 
     * @param string|null $origin The request origin header
     * @return bool
     */
    function isOriginAllowed($origin = null)
    {
        $adexAppKey = env('ADEX_APP_KEY', '');
        $allowedOrigins = array_filter(array_map('trim', explode(',', $adexAppKey)));
        
        // Always add localhost origins in local environment
        if (env('APP_ENV') === 'local') {
            $localhostOrigins = [
                'http://localhost:3000',
                'http://127.0.0.1:3000',
                'http://localhost:8080',
                'http://127.0.0.1:8080',
                'http://localhost:8000',
                'http://127.0.0.1:8000'
            ];
            $allowedOrigins = array_merge($allowedOrigins, $localhostOrigins);
        }
        
        // If origin is null/empty in local environment, allow it
        if (empty($origin) && env('APP_ENV') === 'local') {
            return true;
        }
        
        // Normalize origin (remove trailing slash)
        $origin = rtrim($origin, '/');
        
        return in_array($origin, $allowedOrigins);
    }
}

