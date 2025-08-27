<?php

namespace Mralston\Payment\Traits;

use Mralston\Payment\Interfaces\PaymentParentModel;

trait RedirectsOnActivePayment
{
    protected function redirectToActivePayment(PaymentParentModel $parentModel)
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

    protected function redirectToSelectedOffer(PaymentParentModel $parentModel)
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
}
