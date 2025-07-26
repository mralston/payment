<?php

namespace Mralston\Payment\Listeners;

use Mralston\Payment\Events\OfferReceived;

class StoreOffer
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OfferReceived $event): void
    {
        // TODO: Store offer to the DB
    }
}
