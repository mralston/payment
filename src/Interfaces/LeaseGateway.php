<?php

namespace Mralston\Payment\Interfaces;

use Mralston\Payment\Data\PrequalData;
use Mralston\Payment\Data\PrequalPromiseData;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentSurvey;

interface LeaseGateway
{
    public function apply(Payment $payment): array; // TODO: Change return type to Payment

    public function cancel(Payment $payment): bool;

    public function pollStatus(Payment $payment): array;

    public function prequal(PaymentSurvey $survey): PrequalPromiseData|PrequalData;
}
