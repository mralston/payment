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
        public /*?Carbon*/ ?string $dateStartedEmployment = null,
        public ?BankAccountData $bankAccount = null,
    ) {
        if (is_null($this->employerAddress)) {
            $this->employerAddress = new AddressData();
        }

        if (is_null($this->bankAccount)) {
            $this->bankAccount = new BankAccountData();
        }
    }
}
