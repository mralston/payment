<?php

namespace Mralston\Payment\Interfaces;

interface PaymentGateway
{
    public function getRequestData(): ?array;

    public function getResponseData(): ?array;

    public function healthCheck(): bool;
}
