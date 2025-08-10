<?php

namespace Mralston\Payment\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class BankAccountData extends Data
{
    public function __construct(
        public ?string $bankName = null,
        public ?string $accountName = null,
        public ?string $accountNumber = null,
        public ?string $sortCode = null,
    ) {
        //
    }
}
