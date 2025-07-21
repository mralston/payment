<?php

namespace Mralston\Payment\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 *
 */
interface PaymentParentModel
{
    public function paymentSurvey(): MorphOne;
}
