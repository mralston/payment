<?php

namespace Mralston\Payment\Interfaces;

use Illuminate\Support\Collection;
use Mralston\Payment\Data\AddressLookupResultData;
use Mralston\Payment\Data\AddressLookupResultsData;

interface PaymentAddressLookup
{
    /**
     * @param string $postCode
     * @return Collection<AddressLookupResultData>
     */
    public function lookup(string $postCode): Collection;
}
