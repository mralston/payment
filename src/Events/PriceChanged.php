<?php

namespace Mralston\Payment\Events;

class PriceChanged
{
    public function __construct(
        public int $parent,
        public float $price
    ) {
        //
    }
}
