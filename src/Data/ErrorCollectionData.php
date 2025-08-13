<?php

namespace Mralston\Payment\Data;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class ErrorCollectionData extends Data
{
    /**
     * @param Collection<ErrorData> $errors
     */
    public function __construct(
        public Collection $errors
    ) {
        //
    }
}
