<?php

namespace Mralston\Payment\Interfaces;

use Mralston\Payment\Data\NormalisedResponseData;

interface Response
{
    public function parseResponse(array $responseData): NormalisedResponseData;
}
