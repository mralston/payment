<?php

namespace Mralston\Payment\Traits;

use Mralston\Payment\Interfaces\PaymentHelper;

trait BootstrapsPayment
{
    protected function bootstrap(int $parent, PaymentHelper $helper)
    {
        // Instantiate the parent model using the class specified in the parent application's .env file
        $parentModel = app(config('payment.parent_model'))
            ->findOrFail($parent);

        // Pass the parent model to the helper
        $helper->setParentModel($parentModel);

        return $parentModel;
    }
}
