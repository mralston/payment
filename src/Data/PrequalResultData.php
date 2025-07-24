<?php

namespace Mralston\Payment\Data;

use Illuminate\Support\Collection;
use Mralston\Payment\Models\PaymentSurvey;
use Spatie\LaravelData\Data;

class PrequalResultData extends Data
{
    public function __construct(
        public PaymentSurvey $survey,
        public Collection $products,
    ) {
        //
    }
}
