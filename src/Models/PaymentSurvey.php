<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PaymentSurvey extends Model
{
    protected $fillable = [
        'customers',
        'addresses',
        'basic_questions_completed',
        'lease_questions_completed',
        'finance_questions_completed',
    ];

    protected $casts = [
        'customers' => 'collection',
        'addresses' => 'collection',
    ];

    public function parentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function paymentOffers(): HasMany
    {
        return $this->hasMany(PaymentOffer::class);
    }

    public function getCustomerProperty(int $index, string $property): mixed
    {
        return $this->customers[$index][$property];
    }

    public function setCustomerProperty(int $index, string $property, mixed $value): void
    {
        $customers = $this->customers;
        $customer = $customers[$index];
        $customer[$property] = $value;
        $customers[$index] = $customer;
        $this->customers = $customers;
    }
}
