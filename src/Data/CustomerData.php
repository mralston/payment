<?php

namespace Mralston\Payment\Data;

use Carbon\Carbon;
use Mralston\Payment\Enums\EmploymentStatus;
use Spatie\LaravelData\Data;

class CustomerData extends Data
{
    public function __construct(
        public ?string $title = null,
        public ?string $firstName = null,
        public ?string $middleName = null,
        public ?string $lastName = null,
        public ?string $email = null,
        public ?string $mobile = null,
        public ?string $landline = null,
        public ?Carbon $dateOfBirth = null,
        public ?int $grossAnnualIncome = null,
        public ?int $netMonthlyIncome = null,
        public ?int $dependants = null,
        public ?string $employmentStatus = null,
        public ?string $martialStatus = null,
        public ?string $residentialStatus = null,
        public ?string $nationality = null,
    ) {
        //
    }
}
