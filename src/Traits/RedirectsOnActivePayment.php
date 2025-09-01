<?php

namespace Mralston\Payment\Traits;

use Illuminate\Http\RedirectResponse;
use Mralston\Payment\Interfaces\PaymentParentModel;

trait RedirectsOnActivePayment
{
    protected function redirectToActivePayment(PaymentParentModel $parentModel): void
    {
        // Check to see whether the parent has an active payment
        if (!empty($parentModel->activePayment->paymentOffer)) {

            // Construct a URL to the payment show page
            $url = route('payment.' . $parentModel->activePayment->paymentOffer->type . '.show', [
                'parent' => $parentModel,
                $parentModel->activePayment->paymentOffer->type => $parentModel->activePayment->id,
            ]);

            // Set the redirect header
            header("Location: " . $url);

            // Return redirect response to the browser
            app()->terminate();
        }


    }

    protected function redirectToSelectedOffer(PaymentParentModel $parentModel): void
    {
        // Check to see whether the parent has an active payment
        if (!empty($parentModel->selectedPaymentOffer)) {

            // Construct a URL to the payment show page
            $url = route('payment.' . $parentModel->selectedPaymentOffer->type . '.create', [
                'parent' => $parentModel,
                'offerId' => $parentModel->selectedPaymentOffer->id,
            ]);

            // Set the redirect header
            header("Location: " . $url);

            // Return redirect response to the browser
            app()->terminate();
        }


    }

    protected function redirectIfNewPaymentProhibited(PaymentParentModel $parentModel): void
    {
        if (
            $parentModel
                ->payments()
                ->where('prevent_payment_changes', true)
                ->exists()
        ) {
            // Construct a URL to the payment show page
            $url = route('payment.locked', [
                'parent' => $parentModel
            ]);

            // Set the redirect header
            header("Location: " . $url);

            // Return redirect response to the browser
            app()->terminate();
        }
    }
}
