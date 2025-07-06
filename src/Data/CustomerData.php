<?php

namespace Mralston\Finance\Data;

use Spatie\LaravelData\Data;

class CustomerData extends Data
{
    public function __construct(
        public int $id,
        public string $firstName,
        public string $lastName,
    ) {
        //
    }
}
