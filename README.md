# Finance

## Introduction

Finance application process.

## Installation

```bash
composer require mralston/finance
```

## Configuration

Add the following to your .env file
```dotenv
# Configure the parent model for finance applications 
FINANCE_PARENT_MODEL=\App\Models\Quote

# Optionally configure an Inertia route view from your application
FINANCE_INERTIA_ROOT_VIEW=/layouts/inertia

# Helper class
FINANCE_HELPER=\App\Services\FinanceHelper
```

## Parent Model

Finance records are linked to a parent model in your application; a quotation or contract for example. The parent model must implement the `FinanceParentModel` interface which includes a polymorphic relationship linking the two models. The parent model is declared in the `FINANCE_PARENT_MODEL` configuration variable.

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mralston\Finance\Interfaces\FinanceParentModel;
use Mralston\Finance\Models\FinanceSurvey;

class Quote extends Model implements FinanceParentModel
{
    public function financeSurvey(): MorphOne
    {
        return $this->morphOne(FinanceSurvey::class, 'parentable');
    }
}
```

## Helper

The Finance package relies on a helper class which your application must provide. The helper class implements the `FinanceHelper` interface and allows the Finance package to understand its parent application. The helper class is declared in the `FINANCE_HELPER` configuration variable.

```php
<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mralston\Finance\Data\CustomerData;

class FinanceHelper
{
    protected $parentModel;

    public function setParentModel(Model $parentModel)
    {
        $this->parentModel = $parentModel;
    }

    public function getCustomers(): Collection
    {
        // TODO: Return a collection of CustomerData objects
    }
}
```

## Security Vulnerabilities

Please [e-mail security vulnerabilities directly to me](mailto:matt@mralston.co.uk).

## Licence

PDF is open-sourced software licenced under the [MIT license](LICENSE.md).
