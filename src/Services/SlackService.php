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
                '#text' => $message['text'] ?? '',
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
                    'type' => 'header',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => '🚨 Payment Error Detected',
                    ],
                ],
                [
                    'type' => 'section',
                    'fields' => [
                        [
                            'type' => 'plain_text',
                            'text' => "*Payment ID:* {$payment->id}",
                        ],
                        [
                            'type' => 'plain_text',
                            'text' => "*Parent ID:* {$payment->parentable_id}",
                        ],
                        [
                            'type' => 'plain_text',
                            'text' => "*Provider:* {$payment->paymentProvider->name}",
                        ],
                        [
                            'type' => 'plain_text',
                            'text' => "*Stage:* {$stage}",
                        ],
                    ],
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "*Error Data:* " . substr(json_encode($errorData,  JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 0, 1900),
                    ],
                ],
            ],
        ]);
    }
}