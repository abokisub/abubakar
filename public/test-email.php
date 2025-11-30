<?php
/**
 * Email Configuration Test Script
 * 
 * This script tests your email configuration on cPanel
 * Access it via: https://yourdomain.com/test-email.php
 * 
 * SECURITY: Remove this file after testing!
 */

// Load Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Bootstrap Laravel
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

// Get environment variables
$mailConfig = [
    'MAIL_MAILER' => env('MAIL_MAILER', 'not set'),
    'MAIL_HOST' => env('MAIL_HOST', 'not set'),
    'MAIL_PORT' => env('MAIL_PORT', 'not set'),
    'MAIL_USERNAME' => env('MAIL_USERNAME', 'not set'),
    'MAIL_PASSWORD' => env('MAIL_PASSWORD') ? '***set***' : 'not set',
    'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION', 'not set'),
    'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS', 'not set'),
    'MAIL_FROM_NAME' => env('MAIL_FROM_NAME', 'not set'),
];

// Test email sending
$testResult = null;
$testError = null;

if (isset($_GET['test']) && $_GET['test'] === 'send') {
    try {
        $testEmail = $_GET['email'] ?? env('MAIL_FROM_ADDRESS');
        
        Mail::send('email.verify', [
            'name' => 'Test User',
            'email' => $testEmail,
            'username' => 'testuser',
            'title' => 'Test Email',
            'app_name' => env('APP_NAME', 'Test App'),
            'otp' => '123456'
        ], function ($message) use ($testEmail) {
            $message->to($testEmail)
                    ->subject('Test Email from ' . env('APP_NAME', 'Laravel App'));
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Test'));
        });
        
        if (Mail::failures()) {
            $testResult = 'FAILED';
            $testError = 'Mail failures: ' . implode(', ', Mail::failures());
        } else {
            $testResult = 'SUCCESS';
        }
    } catch (Exception $e) {
        $testResult = 'ERROR';
        $testError = $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Configuration Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        h1 { color: #333; }
        .config-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .config-table th, .config-table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .config-table th { background: #f8f8f8; }
        .status-ok { color: green; font-weight: bold; }
        .status-error { color: red; font-weight: bold; }
        .test-form { margin: 20px 0; padding: 15px; background: #f8f8f8; border-radius: 5px; }
        .test-form input { padding: 8px; margin: 5px; width: 300px; }
        .test-form button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; }
        .test-form button:hover { background: #0056b3; }
        .result { margin: 20px 0; padding: 15px; border-radius: 5px; }
        .result.success { background: #d4edda; color: #155724; }
        .result.error { background: #f8d7da; color: #721c24; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Email Configuration Test</h1>
        
        <div class="warning">
            <strong>⚠️ Security Warning:</strong> Remove this file after testing!
        </div>

        <h2>Current Mail Configuration</h2>
        <table class="config-table">
            <tr>
                <th>Setting</th>
                <th>Value</th>
                <th>Status</th>
            </tr>
            <?php foreach ($mailConfig as $key => $value): ?>
            <tr>
                <td><?php echo $key; ?></td>
                <td><?php echo htmlspecialchars($value); ?></td>
                <td>
                    <?php if ($value === 'not set'): ?>
                        <span class="status-error">❌ Not Set</span>
                    <?php else: ?>
                        <span class="status-ok">✓ Set</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h2>Test Email Sending</h2>
        <div class="test-form">
            <form method="GET">
                <input type="hidden" name="test" value="send">
                <label>Test Email Address:</label><br>
                <input type="email" name="email" value="<?php echo htmlspecialchars(env('MAIL_FROM_ADDRESS', '')); ?>" required>
                <button type="submit">Send Test Email</button>
            </form>
        </div>

        <?php if ($testResult): ?>
        <div class="result <?php echo strtolower($testResult); ?>">
            <strong>Test Result: <?php echo $testResult; ?></strong>
            <?php if ($testError): ?>
                <p><?php echo htmlspecialchars($testError); ?></p>
            <?php else: ?>
                <p>Test email sent successfully! Check your inbox (and spam folder).</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <h2>Common cPanel Email Issues & Solutions</h2>
        <ul>
            <li><strong>Issue:</strong> MAIL_HOST not set correctly
                <br><strong>Solution:</strong> Use your cPanel mail server (usually mail.yourdomain.com or localhost)</li>
            <li><strong>Issue:</strong> Wrong port/encryption
                <br><strong>Solution:</strong> For cPanel, try port 465 with SSL or port 587 with TLS</li>
            <li><strong>Issue:</strong> Authentication failing
                <br><strong>Solution:</strong> Use full email address as MAIL_USERNAME (e.g., support@yourdomain.com)</li>
            <li><strong>Issue:</strong> Emails going to spam
                <br><strong>Solution:</strong> Set up SPF and DKIM records in cPanel</li>
            <li><strong>Issue:</strong> Sendmail not working
                <br><strong>Solution:</strong> Try using 'sendmail' driver instead of 'smtp' on cPanel</li>
        </ul>

        <h2>Recommended cPanel Email Settings</h2>
        <pre style="background: #f8f8f8; padding: 15px; border-radius: 5px; overflow-x: auto;">
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=465
MAIL_USERNAME=support@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=support@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
        </pre>

        <p><strong>Alternative (using sendmail):</strong></p>
        <pre style="background: #f8f8f8; padding: 15px; border-radius: 5px; overflow-x: auto;">
MAIL_MAILER=sendmail
MAIL_SENDMAIL_PATH=/usr/sbin/sendmail -t -i
MAIL_FROM_ADDRESS=support@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
        </pre>
    </div>
</body>
</html>

