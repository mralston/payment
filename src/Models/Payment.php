<?php

namespace Mralston\Payment\Models;

use GregoryDuckworth\Encryptable\EncryptableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mralston\Payment\Interfaces\PaymentParentModel;

class Payment extends Model
{
    use EncryptableTrait;
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
        'monthly_payment',
        'total_payable',
        'payments_breakdown',
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
        'monthly_payment' => 'float',
        'total_payable' => 'float',
        'payments_breakdown' => 'array',
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

    public function setParent(PaymentParentModel $parent): static
    {
        $this->parentable()->associate($parent);

        return $this;
    }

    public function withSurvey(PaymentSurvey $survey): static
    {
        $customer = $survey->customers->first();

        $this->title = $customer['title'];
        $this->first_name = $customer['firstName'];
        $this->middle_name = $customer['middleName'];
        $this->last_name = $customer['lastName'];
        $this->marital_status = $customer['maritalStatus'];
        $this->homeowner_status = $customer['homeowner'];
        $this->has_mortgage = $customer['mortgage'];
        $this->british_citizen = $customer['britishCitizen'];
        $this->date_of_birth = $customer['dateOfBirth'];
        $this->dependents_count = $customer['dependants'];
        // TODO: bankrupt_or_iva
        $this->email_address = $customer['email'];
        $this->primary_telephone = $customer['phone'];
        // TODO: secondary_telephone
        $this->addresses = $survey->addresses;
        $this->employment_status = $customer['employmentStatus'];
        // TODO: employer_name
        // TODO: employer_telephone
        // TODO: employer_address
        // TODO: employer_company_type
        // TODO: employer_company_reg_date
        // TODO: occupation
        // TODO: time_with_employer
        $this->gross_income_individual = $customer['grossAnnualIncome'];
        // TODO: $this->gross_income_household
        $this->net_monthly_income_individual = $customer['netMonthlyIncome'];
        // TODO: mortgage_monthly
        // TODO: rent_monthly
        // TODO: bank_name
        // TODO: bank_account_holder_name

        return $this;
    }

    public function withOffer(PaymentOffer $offer): static
    {
        $this->amount = $offer->amount;
        $this->payment_provider_id = $offer->payment_provider_id;
//        $this->payment_product_id =
        $this->apr = $offer->apr;
        $this->loan_term = $offer->term;
        $this->deferred_period = $offer->deferred;
        $this->first_payment = $offer->first_payment;
        $this->monthly_payment = $offer->monthly_payment;
        $this->final_payment = $offer->final_payment;
        $this->total_payable = $offer->total_payable;

        return $this;
    }
}
