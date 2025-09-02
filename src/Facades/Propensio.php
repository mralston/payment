<?php

namespace Mralston\Payment\Facades;

use App\FinanceApplication;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Mralston\Payment\Integrations\Propensio
 */
class Propensio extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Mralston\Payment\Integrations\Propensio::class;
    }
}
