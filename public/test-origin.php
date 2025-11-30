<?php
/**
 * Origin Validation Diagnostic Tool
 * 
 * This script helps diagnose 403 Forbidden errors by showing:
 * - What origin header is being sent
 * - What ADEX_APP_KEY is configured
 * - Whether they match
 * 
 * Access: https://yourdomain.com/test-origin.php
 * 
 * IMPORTANT: Delete this file after diagnosis for security!
 */

header('Content-Type: application/json');

// Try multiple possible Laravel paths (same logic as index.php)
$possiblePaths = [
    dirname(__DIR__),                    // Standard: public/ -> root
    dirname(dirname(__DIR__)) . '/laravel', // cPanel: public_html -> laravel
    dirname(__DIR__) . '/../',           // Alternative
];

$basePath = null;
foreach ($possiblePaths as $path) {
    $realPath = realpath($path);
    if ($realPath && file_exists($realPath . '/vendor/autoload.php') && file_exists($realPath . '/bootstrap/app.php')) {
        $basePath = $realPath;
        break;
    }
}

$results = [
    'origin_header' => $_SERVER['HTTP_ORIGIN'] ?? 'not set',
    'http_host' => $_SERVER['HTTP_HOST'] ?? 'not set',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'not set',
    'base_path_checked' => $basePath ?: 'not found',
];

if ($basePath && file_exists($basePath . '/vendor/autoload.php')) {
    require_once $basePath . '/vendor/autoload.php';
    
    try {
        $app = require_once $basePath . '/bootstrap/app.php';
        
        $adexAppKey = env('ADEX_APP_KEY', 'not set');
        $allowedOrigins = array_filter(array_map('trim', explode(',', $adexAppKey)));
        
        // Normalize origins
        $normalizedAllowed = array_map(function($url) {
            return strtolower(rtrim(trim($url), '/'));
        }, $allowedOrigins);
        
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $normalizedOrigin = $origin ? strtolower(rtrim($origin, '/')) : 'not set';
        
        $results = array_merge($results, [
            'adex_app_key' => $adexAppKey,
            'allowed_origins' => $allowedOrigins,
            'normalized_allowed' => $normalizedAllowed,
            'normalized_origin' => $normalizedOrigin,
            'match' => $normalizedOrigin !== 'not set' && in_array($normalizedOrigin, $normalizedAllowed),
            'app_env' => env('APP_ENV', 'not set'),
            'app_url' => env('APP_URL', 'not set'),
            'app_debug' => env('APP_DEBUG', 'not set'),
            'laravel_loaded' => true,
        ]);
    } catch (\Exception $e) {
        $results['error'] = 'Could not load Laravel: ' . $e->getMessage();
        $results['trace'] = $e->getTraceAsString();
    }
} else {
    // Fallback: Try to read .env directly
    $envPath = $basePath ? $basePath . '/.env' : dirname(__DIR__) . '/.env';
    $adexAppKey = 'not set (check .env file)';
    
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        if (preg_match('/^ADEX_APP_KEY=(.+)$/m', $envContent, $matches)) {
            $adexAppKey = trim($matches[1]);
        }
    }
    
    $results['error'] = 'Laravel autoload not found. Running in standalone mode.';
    $results['adex_app_key'] = $adexAppKey;
    $results['env_file_path'] = $envPath;
    $results['env_file_exists'] = file_exists($envPath);
}

echo json_encode($results, JSON_PRETTY_PRINT);

