<?php

namespace Mralston\Payment\Services;

use Illuminate\Support\Collection;
use Mralston\Mug\Facades\Mug;
use Mralston\Payment\Data\AddressLookupResultData;
use Mralston\Payment\Data\AddressLookupResultsData;
use Mralston\Payment\Interfaces\PaymentAddressLookup;

class MugService implements PaymentAddressLookup
{
    /**
     * @param string $postCode
     * @return Collection<AddressLookupResultData>
     */
    public function lookup(string $postCode): Collection
    {
        return Mug::addressRecco($postCode)
            ->map(function ($address) {
                return new AddressLookupResultData(
                    uprn: $address['uprn'],
                    latitude: $address['latitude'],
                    longitude: $address['longitude'],
                    summary: $address['text'],
                    houseNumber: null,
                    street: null,
                    address1: $address['addressLine1'],
                    address2: $address['addressLine2'],
                    town: $address['city'],
                    county: $address['county'],
                    postCode: $address['postcode'],
                );
            });
    }
}
