<?php

namespace Mralston\Payment\Data;

use Spatie\LaravelData\Data;

class AddressData extends Data
{
    public function __construct(
        public ?int $udprn = null,
        public ?string $houseNumber = null,
        public ?string $street = null,
        public ?string $address1 = null,
        public ?string $address2 = null,
        public ?string $town = null,
        public ?string $county = null,
        public ?string $postCode = null,
        public ?int $timeAtAddress = 0,
    ) {
        //
    }
}
