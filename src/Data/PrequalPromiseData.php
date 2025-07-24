<?php

namespace Mralston\Payment\Data;

use Mralston\Payment\Models\PaymentSurvey;
use Spatie\LaravelData\Data;

class PrequalPromiseData extends Data
{
    public function __construct(
        public string $gateway,
        public PaymentSurvey $survey,
    ) {
        //
    }
}
