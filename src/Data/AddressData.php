<?php

namespace Mralston\Payment\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class AddressData extends Data
{
    public function __construct(
        public ?string $udprn = null,
        public ?string $uprn = null,
        public ?string $houseNumber = null,
        public ?string $street = null,
        public ?string $address1 = null,
        public ?string $address2 = null,
        public ?string $town = null,
        public ?string $county = null,
        public ?string $postCode = null,
        public ?string $dateMovedIn = null,
        public bool $manual = false,
        public bool $homeAddress = false,
    ) {
        //
    }
}
