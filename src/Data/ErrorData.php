<?php

namespace Mralston\Payment\Data;

use Spatie\LaravelData\Data;

class ErrorData extends Data
{
    public function __construct(
        public string $code,
        public string $message,
    ) {
        //
    }
}
