<?php

namespace Mralston\Payment\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class CompanyData extends Data
{
    public function __construct(
        public ?string $commonName = null,
        public ?string $legalName = null,
        public ?string $companyNumber = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $postCode = null,
        public ?string $depositSortCode = null,
        public ?string $depositAccountNumber = null,
        public ?string $depositAccountName = null,
        public ?string $privacyPolicy = null,
    ) {
        //
    }
}
