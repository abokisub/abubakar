<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP for Login Attempt</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
            color: #333;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
            background-color: #ffffff;
        }
        .logo {
            max-width: 150px;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        .content {
            padding: 30px 20px;
        }
        h1 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin: 0 0 15px 0;
        }
        .greeting {
            font-size: 15px;
            color: #333;
            margin-bottom: 15px;
        }
        .intro-text {
            font-size: 14px;
            color: #666;
            margin-bottom: 25px;
            line-height: 1.7;
        }
        .otp-box {
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            margin: 25px 0;
        }
        .otp-label {
            font-size: 13px;
            color: #666;
            margin-bottom: 10px;
        }
        .otp-code {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            letter-spacing: 4px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
        }
        .details-section {
            margin: 25px 0;
        }
        .details-title {
            font-size: 15px;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
        }
        .details-list {
            list-style: decimal;
            padding-left: 20px;
            margin: 0;
        }
        .details-list li {
            font-size: 13px;
            color: #555;
            margin-bottom: 10px;
            line-height: 1.6;
        }
        .device-info {
            background-color: #f0f4f8;
            border-left: 3px solid #00D9A5;
            padding: 12px 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 13px;
            color: #555;
        }
        .device-info strong {
            color: #333;
        }
        .footer {
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
            background-color: #f8f9fa;
            font-size: 12px;
            color: #999;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            @php
                $logoUrl = rtrim($app_url ?? env('APP_URL', 'https://app.kobopoint.com'), '/') . '/assets/images/logo.png';
            @endphp
            <img src="{{ $logoUrl }}" alt="{{ $app_name ?? 'KoboPoint' }}" class="logo" style="max-width: 150px; height: auto; display: block; margin: 0 auto;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <div style="display: none; font-size: 24px; font-weight: 700; color: #00D9A5;">{{ $app_name ?? 'KoboPoint' }}</div>
        </div>
        
        <div class="content">
            <h1>OTP for Login Attempt</h1>
            
            <div class="greeting">
                Dear {{ ucfirst($name ?? $username ?? 'Valued Customer') }},
            </div>
            
            <div class="intro-text">
                An attempt has been made to login to your {{ $app_name ?? 'KoboPoint' }} account ({{ $email ?? 'your email' }}) and a One-Time Password (OTP) was requested. Please see details below.
            </div>
            
            @if(isset($browser) || isset($ip_address))
            <div class="device-info">
                @if(isset($device_info))
                    <div><strong>Device:</strong> {{ $device_info }}</div>
                @elseif(isset($browser) && isset($os))
                    <div><strong>Browser:</strong> {{ $browser }}</div>
                    <div><strong>Operating System:</strong> {{ $os }}</div>
                @endif
                @if(isset($ip_address))
                    <div><strong>IP Address:</strong> {{ $ip_address }}</div>
                @endif
            </div>
            @endif
            
            <div class="otp-box">
                <div class="otp-label">To complete the login action, please use the One-Time Password (OTP) below:</div>
                <div class="otp-code">{{ $otp }}</div>
            </div>
            
            <div class="details-section">
                <div class="details-title">Details:</div>
                <ol class="details-list">
                    <li>Do not share this OTP with anyone.</li>
                    <li>This One-Time Password (OTP) is valid for one-time use only and expires after 60 seconds.</li>
                    <li>If you did not attempt to log in, please contact our support team at <a href="mailto:{{ $sender_mail ?? 'support@kobopoint.com' }}">{{ $sender_mail ?? 'support@kobopoint.com' }}</a> immediately.</li>
                    <li>For your safety, always ensure the device details match your expectations before entering the OTP.</li>
                </ol>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>{{ $app_name ?? 'KoboPoint' }}</strong></p>
            <p>This is an automated security email. Please do not reply to this email.</p>
            <p>If you have any questions, contact us at <a href="mailto:{{ $sender_mail ?? 'support@kobopoint.com' }}">{{ $sender_mail ?? 'support@kobopoint.com' }}</a></p>
            <p style="margin-top: 10px;">&copy; {{ date('Y') }} {{ $app_name ?? 'KoboPoint' }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

