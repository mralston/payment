<?php

namespace Mralston\Payment\Data;

use Spatie\LaravelData\Data;

class PrequalPromiseData extends Data
{
    public function __construct(
        public string $gateway,
        public string $type,
        public int $surveyId,
    ) {
        //
    }
}
