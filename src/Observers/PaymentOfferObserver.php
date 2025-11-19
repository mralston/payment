<?php

namespace Mralston\Payment\Observers;

use Mralston\Payment\Models\PaymentOffer;

class PaymentOfferObserver
{
    /**
     * Handle the PaymentOffer "created" event.
     */
    public function created(PaymentOffer $paymentOffer): void
    {
        //
    }

    /**
     * Handle the PaymentOffer "updated" event.
     */
    public function updated(PaymentOffer $paymentOffer): void
    {
        //
    }

    /**
     * Handle the PaymentOffer "deleted" event.
     */
    public function deleted(PaymentOffer $paymentOffer): void
    {
        dump($paymentOffer
            ->paymentProvider
            ->gateway());

        // Notify lender of cancellation
        $paymentOffer
            ->paymentProvider
            ->gateway()
            ->cancelOffer($paymentOffer);
    }

    /**
     * Handle the PaymentOffer "restored" event.
     */
    public function restored(PaymentOffer $paymentOffer): void
    {
        //
    }

    /**
     * Handle the PaymentOffer "force deleted" event.
     */
    public function forceDeleted(PaymentOffer $paymentOffer): void
    {
        //
    }
}
