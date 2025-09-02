<?php

namespace Mralston\Payment\Interfaces;

use Mralston\Payment\Models\Payment;

interface Signable
{
    public function signingMethod(): string;

    public function getSigningUrl(Payment $payment): string;
}
