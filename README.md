# Payment

## Introduction

Payment flows for cash, finance and leasing.

## Installation

```bash
composer require mralston/payment
```

## Configuration

Add the following to your .env file
```dotenv
# Configure the parent model for payment records
PAYMENT_PARENT_MODEL=\App\Models\Quote

# Optionally configure an Inertia route view from your application
PAYMENT_INERTIA_ROOT_VIEW=/layouts/inertia

# Helper class
PAYMENT_HELPER=\App\Services\PaymentHelper
```

## Parent Model

Payment records are linked to a parent model in your application; a quotation or contract for example. The parent model must implement the `PaymentParentModel` interface which includes a several relationships. The easiest way to implement the relationship methods is using the `HasPayments` trait. The parent model is declared in the `PAYMENT_PARENT_MODEL` configuration variable.

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mralston\Payment\Interfaces\PaymentParentModel;
use Mralston\Payment\Models\PaymentSurvey;
use Mralston\Payment\Traits\HasPayments;

class Quote extends Model implements PaymentParentModel
{
    use HasPayments;

    public function paymentSurvey(): MorphOne
    {
        return $this->morphOne(PaymentSurvey::class, 'parentable');
    }
}
```

## Helper

The Payment package relies on a helper class which your application must provide. The helper class implements the `PaymentHelper` interface and allows the Payment package to understand its parent application. The helper class is declared in the `PAYMENT_HELPER` configuration variable.

```php
<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mralston\Payment\Data\CustomerData;

class PaymentHelper
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
