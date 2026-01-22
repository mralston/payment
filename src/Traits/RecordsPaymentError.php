<?php

namespace Mralston\Payment\Traits;

use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentStage;
use Mralston\Payment\Services\SlackService;

trait RecordsPaymentError
{
    public function recordError(
        Payment $payment,
        PaymentStage $paymentStage,
        array $data,
    ): void
    {
        $payment->errors()->create([
            'data' => $data,
            'payment_stage_id' => $paymentStage->id,
        ]);

        $slackService = new SlackService();
        $slackService->notifyPaymentError(
            $payment,
            $paymentStage->name,
            $data
        );
    }
}