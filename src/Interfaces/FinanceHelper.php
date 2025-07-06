<?php

namespace Mralston\Finance\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface FinanceHelper
{
    public function setParentModel(Model $parentModel);

    public function getViewData(): array;

    public function getCustomers(): Collection;
}
