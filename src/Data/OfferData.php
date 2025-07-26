<?php

namespace Mralston\Payment\Data;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class OfferData extends Data
{
    public function __construct(
        public int $amount,
        public int $term,
        public float $apr,
        public float $monthlyPayment,
        public ?int $deferred = null,
        public ?float $firstPayment = null,
        public ?float $finalPayment = null,
        public ?Collection $minimumPayments = null,
        public ?string $status = null,
        public ?string $preapprovalId = null,
        public ?int $priority = null,
        public ?string $providerForeignId = null,
    ) {
        //
    }
}
