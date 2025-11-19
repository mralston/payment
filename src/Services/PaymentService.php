<?php

namespace Mralston\Payment\Services;

use Illuminate\Support\Facades\DB;
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
        if ( !$this->isPaymentCompatible($payment, $targetParentable) ) {
            throw new \Exception('Payment is not compatible with the target quote.');
        }

        // Close any payments on the gaining quote
        $this->closePayments($targetParentable);

        // Move the payment
        $this->movePayment($payment, $targetParentable);

        return true;
    }

    public function isPaymentCompatible(Payment $payment, PaymentParentModel $targetParentable): bool
    {
        $helper = app(PaymentHelper::class);
        $helper->setParentModel($targetParentable);

        return (float) $payment->amount + (float) $payment->deposit == $helper->getTotalCost();
    }

    public function closePayments(PaymentParentModel $targetParentable)
    {
        // Cancel and soft delete the payments
        $targetParentable->payments
            ->each(function ($payment) {
                $payment->payment_status_id = PaymentStatus::byIdentifier('cancelled')->id;
                $payment->save();

                $payment->delete();
            });

        // Soft delete the offers
        $targetParentable
            ->paymentSurvey
            ?->paymentOffers
            ->each(function ($offer) {
                $offer->delete();
            });

        // Soft delete the survey
        $targetParentable
            ->paymentSurvey
            ?->delete();
    }

    public function movePayment(Payment $payment, PaymentParentModel $targetParentable): void
    {
        DB::transaction(function () use ($payment, $targetParentable) {
            // Grab the survey
            $survey = $payment
                ->parentable
                ->paymentSurvey;

            // Move the payment to the new parent
            $payment->parentable_id = $targetParentable->id;
            $payment->save();

            if ($survey) {
                // Move the survey to the new parent
                $survey->parentable_id = $targetParentable->id;
                $survey->save();

                // Move the offers to the new parent
                $survey
                    ->paymentOffers
                    ->each(function ($offer) use ($targetParentable) {
                        $offer->parentable_id = $targetParentable->id;
                        $offer->save();
                    });
            }
        });
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
