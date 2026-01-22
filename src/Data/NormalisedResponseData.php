<?php

namespace Mralston\Payment\Data;

use Spatie\LaravelData\Data;

class NormalisedResponseData extends Data
{
    public function __construct(
        public ?int $httpStatus = null,
        public ?string $requestId = null,
        public ?string $applicationId = null,
        public ?string $applicationNumber = null,
        public ?int $statusId = null,
        public ?string $statusName = null,
    ) {
    }
}