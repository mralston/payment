<?php

namespace Mralston\Payment\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class FinanceData extends Data
{
    public function __construct(
        public ?string $employerName = null,
        public ?AddressData $employerAddress = null,
        public ?string $occupation = null,
        public ?Carbon $dateStartedEmployment = null,
    ) {
        if (is_null($this->employerAddress)) {
            $this->employerAddress = new AddressData();
        }
    }
}
