<?php

namespace Mralston\Payment\Services;

use Mralston\Payment\Dto\CancellationDto;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentCancellation;
use Mralston\Payment\Models\PaymentStatus;
use Mralston\Payment\Events\PaymentCancelled;

class PaymentService
{
    public function cancel(CancellationDto $dto)
    {
        $payment = Payment::find($dto->paymentId);

        $payment->update([
            'payment_status_id' => PaymentStatus::byIdentifier($dto->paymentStatusIdentifier)?->id,
        ]);

        PaymentCancellation::create([
            'payment_id' => $dto->paymentId,
            'user_id' => $dto->userId,
            'reason' => $dto->reason,
            'source' => $dto->source,
        ]);

        // if (method_exists($payment->paymentProvider, 'gateway')) {
        //     $payment
        //         ->paymentProvider
        //         ->gateway()
        //         ->cancel($payment);
        // }

        event(new PaymentCancelled($payment));
    }
}