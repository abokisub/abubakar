<?php
/**
 * Test endpoint to debug network API issues
 * Access: https://app.kobopoint.com/test-network-api.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

// Get config values
$appKey = config('adex.app_key');
$deviceKey = config('adex.device_key');
$error500 = config('adex.error_500');

// Get origin from request
$origin = $request->headers->get('origin', 'Not set');
$authorization = $request->header('Authorization', 'Not set');

// Normalize origin
$allowedOrigins = array_filter(array_map('trim', explode(',', $appKey ?: '')));
$originNormalized = rtrim($origin ?: '', '/');

// Check if origin matches
$originMatch = in_array($originNormalized, $allowedOrigins);
$deviceKeyMatch = ($deviceKey === $authorization);

// Get networks from database
try {
    $networks = Illuminate\Support\Facades\DB::table('network')->select('network', 'plan_id')->get();
    $networkCount = $networks->count();
} catch (\Exception $e) {
    $networks = [];
    $networkCount = 0;
    $dbError = $e->getMessage();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Network API Debug</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #00D9A5; padding-bottom: 10px; }
        .section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #00D9A5; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: #666; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #00D9A5; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Network API Debug Information</h1>
        
        <div class="section">
            <h2>Configuration Values</h2>
            <table>
                <tr>
                    <th>Setting</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>ADEX_APP_KEY</td>
                    <td><pre><?php echo htmlspecialchars($appKey ?: 'NOT SET'); ?></pre></td>
                </tr>
                <tr>
                    <td>ADEX_DEVICE_KEY</td>
                    <td><pre><?php echo htmlspecialchars($deviceKey ? 'SET (' . strlen($deviceKey) . ' chars)' : 'NOT SET'); ?></pre></td>
                </tr>
                <tr>
                    <td>ERROR_500</td>
                    <td><?php echo htmlspecialchars($error500); ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Request Headers</h2>
            <table>
                <tr>
                    <th>Header</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>Origin</td>
                    <td><?php echo htmlspecialchars($origin); ?></td>
                </tr>
                <tr>
                    <td>Origin (Normalized)</td>
                    <td><?php echo htmlspecialchars($originNormalized); ?></td>
                </tr>
                <tr>
                    <td>Authorization</td>
                    <td><?php echo htmlspecialchars($authorization !== 'Not set' ? 'SET (' . strlen($authorization) . ' chars)' : 'NOT SET'); ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Origin Validation</h2>
            <table>
                <tr>
                    <th>Check</th>
                    <th>Result</th>
                </tr>
                <tr>
                    <td>Allowed Origins</td>
                    <td><pre><?php echo htmlspecialchars(implode("\n", $allowedOrigins ?: ['None'])); ?></pre></td>
                </tr>
                <tr>
                    <td>Origin Match</td>
                    <td class="<?php echo $originMatch ? 'success' : 'error'; ?>">
                        <?php echo $originMatch ? '✓ MATCH' : '✗ NO MATCH'; ?>
                    </td>
                </tr>
                <tr>
                    <td>Device Key Match</td>
                    <td class="<?php echo $deviceKeyMatch ? 'success' : 'error'; ?>">
                        <?php echo $deviceKeyMatch ? '✓ MATCH' : '✗ NO MATCH'; ?>
                    </td>
                </tr>
                <tr>
                    <td>Overall Validation</td>
                    <td class="<?php echo ($originMatch || $deviceKeyMatch) ? 'success' : 'error'; ?>">
                        <?php echo ($originMatch || $deviceKeyMatch) ? '✓ PASS' : '✗ FAIL'; ?>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Database Networks</h2>
            <?php if (isset($dbError)): ?>
                <p class="error">Database Error: <?php echo htmlspecialchars($dbError); ?></p>
            <?php else: ?>
                <p class="info">Total Networks: <strong><?php echo $networkCount; ?></strong></p>
                <?php if ($networkCount > 0): ?>
                    <table>
                        <tr>
                            <th>Network</th>
                            <th>Plan ID</th>
                        </tr>
                        <?php foreach ($networks as $net): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($net->network); ?></td>
                                <td><?php echo htmlspecialchars($net->plan_id); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p class="error">No networks found in database!</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>Recommendations</h2>
            <ul>
                <?php if (!$appKey): ?>
                    <li class="error">Set ADEX_APP_KEY in .env file</li>
                <?php endif; ?>
                <?php if (!$originMatch && !$deviceKeyMatch): ?>
                    <li class="error">Origin validation is failing. Check that ADEX_APP_KEY in .env matches your domain (https://app.kobopoint.com)</li>
                <?php endif; ?>
                <?php if ($networkCount === 0): ?>
                    <li class="error">No networks in database. Import network data from localhost.</li>
                <?php endif; ?>
                <?php if ($appKey && ($originMatch || $deviceKeyMatch) && $networkCount > 0): ?>
                    <li class="success">Configuration looks good! If networks still don't show, check browser console for errors.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html>

