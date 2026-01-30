<?php

namespace Mralston\Payment\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Mralston\Payment\Interfaces\PaymentParentModel;

interface OfferVisibilityRule
{
    /**
     * Apply visibility constraints to the payment offers query.
     * Only add constraints; don’t replace the whole query.
     */
    public function showOffers(Builder|Relation $query, PaymentParentModel $parent): void;
}
