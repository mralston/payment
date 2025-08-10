<?php

namespace Mralston\Payment\Models;

use GregoryDuckworth\Encryptable\EncryptableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mralston\Payment\Data\BankAccountData;
use Mralston\Payment\Data\FinanceData;

class PaymentSurvey extends Model
{
    use EncryptableTrait;

    protected $fillable = [
        'customers',
        'addresses',
        'basic_questions_completed',
        'lease_questions_completed',
        'finance_questions_completed',
        'finance_responses',
        'lease_responses',
    ];

    protected $casts = [
        'customers' => 'collection',
        'addresses' => 'collection',
        'finance_responses' => FinanceData::class,
        //'lease_responses' => LeaseData::class,
    ];

    protected $encryptable = [
//        'finance_responses',
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
