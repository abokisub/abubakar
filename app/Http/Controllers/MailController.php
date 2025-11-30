<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Exception;
use Illuminate\Support\Facades\Log;

class MailController extends Controller
{
    // adex developer
    public static function send_mail($user_data, $template)
    {
        try {
            // Validate required email data
            if (!isset($user_data['email']) || empty($user_data['email'])) {
                Log::error('Mail sending failed: Email address is missing', ['user_data' => $user_data]);
                return false;
            }

            // Validate email format
            if (!filter_var($user_data['email'], FILTER_VALIDATE_EMAIL)) {
                Log::error('Mail sending failed: Invalid email format', ['email' => $user_data['email']]);
                return false;
            }

            // Check if template exists
            if (!view()->exists($template)) {
                Log::error('Mail sending failed: Email template not found', ['template' => $template]);
                return false;
            }

            // Get mail configuration
            $fromAddress = env('MAIL_FROM_ADDRESS', config('mail.from.address'));
            $fromName = $user_data['app_name'] ?? env('MAIL_FROM_NAME', config('mail.from.name'));

            if (empty($fromAddress)) {
                Log::error('Mail sending failed: MAIL_FROM_ADDRESS is not configured');
                return false;
            }

            // Log email attempt (without sensitive data)
            Log::info('Attempting to send email', [
                'to' => $user_data['email'],
                'template' => $template,
                'from' => $fromAddress,
                'subject' => $user_data['title'] ?? 'No Subject'
            ]);

            // Send email
            Mail::send($template, $user_data, function ($message) use ($user_data, $fromAddress, $fromName) {
                $message->to($user_data['email'], $user_data['username'] ?? $user_data['name'] ?? 'User')
                        ->subject($user_data['title'] ?? 'Notification');
                
                // Set from address
                if (!empty($fromAddress)) {
                    $message->from($fromAddress, $fromName);
                }
            });

            // Check for failures
            if (Mail::failures()) {
                $failures = Mail::failures();
                Log::error('Mail sending failed', [
                    'email' => $user_data['email'],
                    'failures' => $failures,
                    'template' => $template
                ]);
                return false;
            }

            Log::info('Email sent successfully', [
                'to' => $user_data['email'],
                'template' => $template
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Mail sending exception', [
                'email' => $user_data['email'] ?? 'unknown',
                'template' => $template,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
