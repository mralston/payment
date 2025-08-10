<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Support\Collection;
use Mralston\Mug\Facades\Mug;

class AddressLookupController
{
    public function lookup(string $postcode): Collection
    {
        // Get list of addresses for postcode
        $addressList = Mug::addressRecco($postcode);

        return $addressList;
    }

}
