<?php

namespace Mralston\Payment\Interfaces;

use Mralston\Payment\Models\Payment;

interface WantsEpvs
{
    public function uploadEpvsCertificate(Payment $payment, string $encodedFile): bool;
}
