<?php

namespace Mralston\Payment\Interfaces;

use Illuminate\Support\Collection;
use Mralston\Payment\Data\ErrorCollectionData;

interface ParsesErrors
{
    public function parseErrors(Collection $response): ErrorCollectionData;
}
