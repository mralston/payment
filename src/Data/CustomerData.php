<?php

namespace Mralston\Finance\Data;

use Carbon\Carbon;
use Mralston\Finance\Enums\EmploymentStatus;
use Spatie\LaravelData\Data;

class CustomerData extends Data
{
    public function __construct(
        public ?string $title = null,
        public ?string $firstName = null,
        public ?string $middleName = null,
        public ?string $lastName = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?Carbon $dateOfBirth = null,
        public ?int $grossAnnualIncome = null,
        public ?int $netMonthlyIncome = null,
        public ?int $dependants = null,
        public ?EmploymentStatus $employmentStatus = null,
    ) {
        //
    }
}
