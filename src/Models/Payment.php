<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'reference',
        'quote_id',
        'total_price',
        'loan_amount',
        'deposit',
        'subsidy',
        'finance_lender_id',
        'finance_rate_id',
        'apr',
        'loan_term',
        'deferred_period',
        'monthly_repayment',
        'total_repayable',
        'repayments_breakdown',
        'hypothetical_column',
        'hypothetical_total_price',
        'hypothetical_loan_amount',
        'hypothetical_monthly_repayment',
        'hypothetical_total_repayable',
        'hypothetical_repayments_breakdown',
        'eligible',
        'gdpr_opt_in',
        'title',
        'first_name',
        'middle_name',
        'last_name',
        'marital_status',
        'homeowner_status',
        'has_mortgage',
        'british_citizen',
        'date_of_birth',
        'dependents_count',
        'bankrupt_or_iva',
        'email_address',
        'primary_telephone',
        'secondary_telephone',
        'addresses',
        'employment_status',
        'employer_ibc_ref',
        'employer_name',
        'employer_telephone',
        'employer_address',
        'employer_company_type',
        'employer_company_reg_date',
        'occupation',
        'time_with_employer',
        'gross_income_individual',
        'gross_income_household',
        'net_monthly_income_individual',
        'mortgage_monthly',
        'rent_monthly',
        'bank_name',
        'bank_account_holder_name',
        'bank_account_number',
        'bank_account_sort_code',
        'read_terms_conditions',
        'was_referred',
        'status',
        'lender_application_id',
        'lender_request_data',
        'lender_response_data',
        'offer_expiration_date',
        'submitted_at',
        'decision_received_at',
        'signed_at',
        'sat_note_file_id',
        'credit_agreement_file_id',
    ];

    protected $casts = [
        'total_price' => 'float',
        'loan_amount' => 'float',
        'deposit' => 'float',
        'subsidy' => 'float',
        'apr' => 'float',
        'loan_term' => 'integer',
        'deferred_period' => 'integer',
        'monthly_repayment' => 'float',
        'total_repayable' => 'float',
        'repayments_breakdown' => 'array',
        'dependents_count' => 'integer',
        'addresses' => 'collection',
        'employer_address' => 'collection',
        'time_with_employer' => 'integer',
        'gross_income_individual' => 'float',
        'gross_income_household' => 'float',
        'net_monthly_income_individual' => 'float',
        'mortgage_monthly' => 'float',
        'rent_monthly' => 'float',
        'lender_response_data' => 'collection',
        'date_of_birth' => 'datetime',
        'offer_expiration_date' => 'datetime',
        'submitted_at' => 'datetime',
        'decision_received_at' => 'datetime',
        'signed_at' => 'datetime',
        'employer_company_reg_date' => 'datetime',
    ];

    protected $encryptable = [
        'bank_account_number',
        'bank_account_sort_code',
    ];

    protected $expirable = [
        'new',
        'pending',
        'referred',
        'conditional_accept',
        'accepted',
        'parked',
        'snagged',
    ];

    public function parentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function paymentProvider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class);
    }

    public function paymentStatus(): BelongsTo
    {
        return $this->belongsTo(PaymentStatus::class);
    }
}
