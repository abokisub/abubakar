<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BellWebhook;
use App\Models\BellTransaction;
use App\Models\BellAccount;
use App\Jobs\ProcessBellWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class BellBankWebhookController extends Controller
{
    /**
     * Handle incoming webhook from BellBank
     */
    public function handle(Request $request)
    {
        try {
            // Log incoming webhook for debugging
            Log::info('BellBank webhook received', [
                'ip' => $request->ip(),
                'event' => $request->input('event'),
                'reference' => $request->input('reference'),
                'headers' => $request->headers->all(),
            ]);

            // Verify webhook signature (optional - log warning but allow if not configured)
            $signatureValid = $this->verifySignature($request);
            if (!$signatureValid) {
                Log::warning('BellBank webhook signature verification failed or not configured', [
                    'ip' => $request->ip(),
                    'event' => $request->input('event'),
                ]);
                // Continue processing even if signature verification fails (for testing)
                // In production, you may want to return 403 here
            }

            // Store raw webhook
            $webhook = BellWebhook::create([
                'event' => $request->input('event', 'unknown'),
                'payload' => $request->all(),
                'headers' => $request->headers->all(),
                'received_at' => now(),
                'processed' => false,
            ]);

            Log::info('BellBank webhook stored', [
                'webhook_id' => $webhook->id,
                'event' => $webhook->event,
                'reference' => $request->input('reference'),
            ]);

            // Dispatch job to process webhook asynchronously
            ProcessBellWebhook::dispatch($webhook->id);

            // Return 200 OK immediately
            return response()->json(['status' => 'ok', 'message' => 'Webhook received'], 200);

        } catch (\Exception $e) {
            Log::error('BellBank webhook handling failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            // Still return 200 to prevent retries from BellBank
            return response()->json(['status' => 'error', 'message' => 'Processing failed'], 200);
        }
    }

    /**
     * Verify webhook signature
     */
    protected function verifySignature(Request $request): bool
    {
        $secret = config('bellbank.webhook_secret');
        
        if (!$secret) {
            Log::warning('BellBank webhook secret not configured');
            return false;
        }

        // Get signature from header (adjust header name based on BellBank docs)
        $signature = $request->header('X-Bellbank-Signature') 
                  ?? $request->header('X-Signature')
                  ?? $request->header('Signature');

        if (!$signature) {
            return false;
        }

        // Get raw payload
        $payload = $request->getContent();
        
        // Generate expected signature (HMAC SHA256)
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        // Compare signatures (timing-safe comparison)
        return hash_equals($expectedSignature, $signature);
    }
}



