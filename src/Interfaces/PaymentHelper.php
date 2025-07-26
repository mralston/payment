<?php

namespace Mralston\Payment\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface PaymentHelper
{
    public function setParentModel(Model $parentModel);

    public function getViewData(): array;

    public function getCustomers(): Collection;

    public function getTotalCost();

    public function getDeposit();
}
