<?php

namespace Mralston\Payment\Events;

use Event;
use Mralston\Payment\Models\Payment;

class PaymentCancelled extends Event
{
    public function __construct(
        public Payment $payment,
        public ?string $reason = null,
    ) {
        //
    }
}
