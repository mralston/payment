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
        public ?string $dateStartedEmployment = null,
        public ?BankAccountData $bankAccount = null,
        public ?float $yearlyHouseholdIncome = null,
        public ?float $monthlyMortgage = null,
        public ?float $monthlyRent = null,
    ) {
        if (is_null($this->employerAddress)) {
            $this->employerAddress = new AddressData();
        }

        if (is_null($this->bankAccount)) {
            $this->bankAccount = new BankAccountData();
        }
    }
}
