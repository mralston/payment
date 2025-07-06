<?php

namespace Mralston\Finance\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 *
 */
interface FinanceParentModel
{
    public function financeSurvey(): MorphOne;
}
