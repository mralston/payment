<?php

namespace Mralston\Payment\Services;

use Mralston\Payment\Data\CancellationData;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentCancellation;
use Mralston\Payment\Models\PaymentStatus;
use Mralston\Payment\Events\PaymentCancelled;

class PaymentService
{
    public function cancel(CancellationData $dto)
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

        // Delete related offers from DB (they will have been cancelled by some providers)
        $payment->parentable
            ->paymentOffers()
            ->delete();

        event(new PaymentCancelled($payment, $dto->reason));
    }
}
