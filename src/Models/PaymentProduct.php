<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'identifier',
        'provider_foreign_id',
        'apr',
        'monthly_rate',
        'term',
        'deferred',
        'service_fee',
        'document_fee',
        'document_fee_percentage',
        'document_fee_minimum',
        'document_fee_maximum',
        'document_fee_collection_month',
        'min_loan',
        'max_loan',
        'settlement_fee',
        'deferred_type',
        'sort_order',
    ];
}
