<?php

namespace Mralston\Payment\Data;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class AddressLookupResultsData extends Data
{
    /**
     * @param Collection<AddressLookupResultData> $addresses
     */
    public function __construct(
        public Collection $addresses,
    ) {
        //
    }
}
