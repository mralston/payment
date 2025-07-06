<?php

namespace Mralston\Finance\Traits;

use Mralston\Finance\Interfaces\FinanceHelper;

trait BootstrapsFinance
{
    protected function bootstrap(int $parent, FinanceHelper $helper)
    {
        // Instantiate the parent model using the class specified in the parent application's .env file
        $parentModel = app(config('finance.parent_model'))
            ->findOrFail($parent);

        // Pass the parent model to the helper
        $helper->setParentModel($parentModel);

        return $parentModel;
    }
}
