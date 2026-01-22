<?php
// src/Services/SlackService.php
namespace Mralston\Payment\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mralston\Payment\Models\Payment;

class SlackService
{
    public function send(string $webhookUrl, array $message): bool
    {
        try {
            $response = Http::post($webhookUrl, [
                'text' => $message['text'] ?? '',
                'blocks' => $message['blocks'] ?? [],
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::channel('payment')->error('Slack notification failed: ' . $e->getMessage());
            return false;
        }
    }

    public function notifyPaymentError(Payment $payment, string $stage, array $errorData): void
    {
        $webhookUrl = config('payment.slack.webhook_url');
        
        if (!$webhookUrl) {
            return;
        }

        $this->send($webhookUrl, [
            'text' => "🚨 Payment Error: #{$payment->id}",
            'blocks' => [
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "*Payment Error Detected*\n*Payment ID:* {$payment->id}\n *Parent ID:* {$payment->parentable_id}\n*Stage:* {$stage}\n*Provider:* {$payment->paymentProvider->name}\n" .
                        "*Error Data:* " . json_encode($errorData),
                    ],
                ],
            ],
        ]);
    }
}