<?php

namespace Mralston\Payment\Interfaces;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentSurvey;

/**
 * @property PaymentSurvey paymentSurvey
 */
interface PaymentParentModel
{
    public function paymentSurvey(): MorphOne;

    public function payments(): MorphMany;

    public function activePayment(): MorphOne;
}
