<?php

namespace Mralston\Payment\Interfaces;

interface Signable
{
    public function getSigningUrl(Payment $payment): string;
}
