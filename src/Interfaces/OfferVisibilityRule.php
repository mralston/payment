<?php

namespace Mralston\Payment\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Mralston\Payment\Interfaces\PaymentParentModel;

interface OfferVisibilityRule
{
    public function applyVisibilityConstraints(
        Builder|Relation $query,
        PaymentParentModel $parent
    ): void;
}
