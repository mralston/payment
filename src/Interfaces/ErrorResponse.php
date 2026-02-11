<?php

namespace Mralston\Payment\Interfaces;

interface ErrorResponse
{
    public function parseErrors(array $responseData): array;
}
