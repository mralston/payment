<?php

namespace Mralston\Payment\Services;

use Mralston\Payment\Data\CancellationData;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentCancellation;
use Mralston\Payment\Models\PaymentStatus;

class PaymentService
{
    public function cancel(CancellationData $dto)
    {
        $payment = Payment::find($dto->paymentId);

        $payment->update([
            'payment_status_id' => PaymentStatus::byIdentifier($dto->paymentStatusIdentifier)?->id,
        ]);

        PaymentCancellation::create([
            'payment_id' => $payment->id,
            'user_id' => $dto->userId,
            'reason' => $dto->reason,
            'source' => $dto->source,
        ]);
    }
}
