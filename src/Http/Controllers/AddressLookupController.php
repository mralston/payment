<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Support\Collection;
use Mralston\Payment\Data\AddressLookupResultData;
use Mralston\Payment\Interfaces\PaymentAddressLookup;

class AddressLookupController
{
    public function __construct(
        protected PaymentAddressLookup $lookupService
    ) {
        //
    }

    /**
     * @param string $postCode
     * @return Collection<AddressLookupResultData>
     */
    public function lookup(string $postcode): Collection
    {
        return $this->lookupService->lookup($postcode);
    }

}
