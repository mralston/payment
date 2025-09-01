<?php

namespace Mralston\Payment\Traits;

use Illuminate\Http\RedirectResponse;
use Mralston\Payment\Interfaces\PaymentParentModel;

trait RedirectsOnActivePayment
{
    protected function redirectToActivePayment(PaymentParentModel $parentModel): ?RedirectResponse
    {
        // Check to see whether the parent has an active payment
        if (!empty($parentModel->activePayment->paymentOffer)) {
            return redirect()->route(
                'payment.' . $parentModel->activePayment->paymentOffer->type . '.show',
                [
                    'parent' => $parentModel,
                    $parentModel->activePayment->paymentOffer->type => $parentModel->activePayment->id,
                ]
            );
        }

        return null;
    }

    protected function redirectToSelectedOffer(PaymentParentModel $parentModel): ?RedirectResponse
    {
        // Check to see whether the parent has a selected offer
        if (!empty($parentModel->selectedPaymentOffer)) {
            return redirect()->route(
                'payment.' . $parentModel->selectedPaymentOffer->type . '.create',
                [
                    'parent' => $parentModel,
                    'offerId' => $parentModel->selectedPaymentOffer->id,
                ]
            );
        }

        return null;
    }
}
