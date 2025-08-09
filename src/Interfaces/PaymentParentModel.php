<?php

namespace Mralston\Payment\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Mralston\Payment\Models\PaymentSurvey;

/**
 * @property PaymentSurvey paymentSurvey
 */
interface PaymentParentModel
{
    public function paymentSurvey(): MorphOne;
}
