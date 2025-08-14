<?php

namespace Mralston\Payment\Data;

use Spatie\LaravelData\Data;

class CancellationData extends Data
{
    public function __construct(
        public string $paymentId,
        public string $paymentStatusIdentifier,
        public string $reason,
        public string $source,
        public string $userId,
    ) {
        //
    }
}
