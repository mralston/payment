<?php

namespace Mralston\Payment\Interfaces;

use Mralston\Payment\Models\Payment;

interface WantsSatNote
{
    public function sendSatNote(Payment $payment): bool;
}
