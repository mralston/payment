<?php

namespace Mralston\Payment\Services;

use Mralston\Payment\Data\CancellationData;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentCancellation;
use Mralston\Payment\Models\PaymentStatus;
use Mralston\Payment\Events\PaymentCancelled;
use Mralston\Payment\Interfaces\PaymentParentModel;
use Mralston\Payment\Interfaces\PaymentHelper;

class PaymentService
{
    public function cancel(CancellationData $dto)
    {
        $payment = Payment::find($dto->paymentId);

        $payment->update([
            'payment_status_id' => PaymentStatus::byIdentifier($dto->paymentStatusIdentifier)?->id,
            'prevent_payment_changes' => $dto->disableChangePaymentMethodAfterCancellation,
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

    public function move(Payment $payment, PaymentParentModel $targetParentable): bool
    {
        if ( !$this->isFinanceApplicationCompatible($payment, $targetParentable) ) {
            throw new \Exception('Finance application is not compatible with the target quote.');
        }

        //close any finance applications on the gaining quote
        $this->closePayments($targetParentable);

        //move the finance application
        $this->movePayment($payment, $targetParentable);

        return true;
    }

    public function isFinanceApplicationCompatible(Payment $payment, PaymentParentModel $targetParentable): bool
    {
        $helper = app(PaymentHelper::class);
        $helper->setParentModel($targetParentable);

        return (float) $payment->amount + (float) $payment->deposit == $helper->getTotalCost();
    }

    public function closePayments(PaymentParentModel $targetParentable)
    {
        foreach( $targetParentable->payments as $payment ) {
            $payment->payment_status_id = PaymentStatus::byIdentifier('cancelled')->id;
            $payment->save();

            $payment->delete();
        }
    }

    public function movePayment(Payment $payment, PaymentParentModel $targetParentable)
    {
        $payment->parentable_id = $targetParentable->id;
        $payment->save();
    }

    public function paymentCompatibility(Payment $payment, PaymentParentModel $targetParentable): array
    {
        $helper = app(PaymentHelper::class);
        $helper->setParentModel($targetParentable);

        return [
            'parentable_total_cost' => $helper->getTotalCost(),
            'payment_amount' => (float) $payment->amount + $payment->deposit,
        ];
    }
}
