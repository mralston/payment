<?php

namespace Mralston\Payment\Dto;

class CancellationDto
{
    public function __construct(
        public string $paymentId,
        public string $paymentStatusIdentifier,
        public string $reason,
        public string $source,
        public string $userId,
    ) {}
}
