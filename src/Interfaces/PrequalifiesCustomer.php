<?php

namespace Mralston\Payment\Interfaces;

use Mralston\Payment\Data\PrequalData;
use Mralston\Payment\Data\PrequalPromiseData;
use Mralston\Payment\Models\PaymentSurvey;

interface PrequalifiesCustomer
{
    public function prequal(PaymentSurvey $survey): PrequalPromiseData|PrequalData;
}
