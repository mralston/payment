<?php

namespace Mralston\Payment\Observers;

use Illuminate\Support\Str;
use Mralston\Payment\Models\Payment;

class PaymentObserver
{
    public function creating(Payment $payment): void
    {
        $payment->uuid = Str::uuid();
    }
}
