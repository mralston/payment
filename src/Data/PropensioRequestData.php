<?php

namespace Mralston\Payment\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentLookupField;
use Mralston\Payment\Enums\LookupField;

class PropensioRequestData extends Data
{
    private array $requestData = [];
    
    public function __construct(
        public Payment $payment,
        public string $environmentCode,
        public string $reference,
        public float $gross,
    ) {
        //
    }

    public function build(): void
    {
        $salesRep = $this->payment->parentable->user;

        $addresses = [];
        $previousAddressIndex = 1;
        foreach ($this->payment->addresses as $index => $address) {
            $timeAtAddress = Carbon::now()->diff(Carbon::parse($address['dateMovedIn'] . ' 00:00:00'));
            if($address['homeAddress']) {
                $addresses['currentAddress'] = $addresses['installationAddress'] = [
                    'buildingSubName' => '',
                    'buildingName' => '',
                    'buildingNo' => $address['houseNumber'] ?? '',
                    'addressLine1' => $address['street'] ?? '',
                    'addressLine2' => $address['address1'] ?? '',
                    'city' => !empty($address['address2']) ?
                        $address['address2'] : $address['county'],
                    'county' => $address['county'] ?? '',
                    'postcode' => $address['postCode'] ?? '',
                    'timeYears' => (string) $timeAtAddress->y,
                    'timeMonths' => (string) $timeAtAddress->m
                ];
            } else {
                $key = 'previousAddress' . $previousAddressIndex++;
                $addresses[$key] = [
                    'buildingSubName' => '',
                    'buildingName' => '',
                    'buildingNo' => $address['houseNumber'] ?? '',
                    'addressLine1' => $address['street'] ?? '',
                    'addressLine2' => $address['address1'] ?? '',
                    'city' => !empty($address['address2']) ?
                        $address['address2'] : $address['county'],
                    'county' => $address['county'] ?? '',
                    'postcode' => $address['postCode'] ?? '',
                    'timeYears' => (string) $timeAtAddress->y,
                    'timeMonths' => (string) $timeAtAddress->m
                ];
            }
        }

        $this->requestData = [
            'environmentCode' => $this->environmentCode,
            'apiFormCode' => 'S03_RETAIL', //config('payment.propensio.api_form_code'),
            'hostSystemApplicationRef' => $this->reference,
            'loan' => [
                'mediaCode' => config('payment.propensio.media_code'),
                'productCode' => $this->payment->paymentProduct->provider_foreign_id,
                'introducersReference' => config('payment.propensio.introducers_reference'),
                'goodsHostCode' => config('payment.propensio.goods_host_code'),
                'loanPurposeHostCode' => config('payment.propensio.loan_purpose_host_code'),
                'cashPriceAmount' => $this->gross,
                'depositAmount' => $this->payment->deposit,
                'additionalContribution' => 0,
                'loanAmount' => round($this->payment->amount - $this->payment->deposit, 2),
                'repaymentTermInMonths' => $this->payment->term,
                'partExchangeAmount' => 0,
                'settlementValueAmount' => 0,
                'consentToCreditSearch' => true,
                'consentToTerms' => true,
                'preferredPaymentDay' => 12,
                'salespersonEmail' => $salesRep->email,
                'installerName' => $salesRep->name,
                'assetType' => 'NO ASSET',
                // 'assetMake' => 'Willerby',
                // 'assetModel' => 'Rio',
                // 'assetSerialNumber' => '1234567890ABCDEFG',
                // 'assetCondition' => 'NEW',
                'notes' => 'Additional notes',
                'alertNotes' => 'This is an alert note text when the app was created'
            ],
            'applicant' => [
                'titleCode' => PaymentLookupField::byIdentifier(LookupField::TITLE)
                    ?->paymentLookupValues()
                    ->where('value', $this->payment->title)
                    ->first()
                    ?->payment_provider_values['propensio'] ?? 'MR',
                'firstName' => $this->payment->first_name ?? '',
                'middleName' => $this->payment->middle_name ?? '',
                'lastName' => $this->payment->last_name ?? '',
                'dateOfBirth' => $this->payment->date_of_birth?->format('Y-m-d') ?? '',
                'email' => $this->payment->email_address ?? '',
                'mobilePhone' => $this->payment->primary_telephone ?? '',
                'homePhone' => $this->payment->secondary_telephone ?? '',
                'residentialStatusCode' => PaymentLookupField::byIdentifier(LookupField::RESIDENTIAL_STATUS)
                    ?->paymentLookupValues()
                    ->where('value', $this->payment->residential_status)
                    ->first()
                    ?->payment_provider_values['propensio'] ?? 'H',
                'maritalStatusCode' => PaymentLookupField::byIdentifier(LookupField::MARITAL_STATUS)
                    ?->paymentLookupValues()
                    ->where('value', $this->payment->marital_status)
                    ->first()
                    ?->payment_provider_values['propensio'] ?? 'M',
                'numberOfDependents' => $this->payment->dependents ?? '2',
                'isVulnerable' => true,
                'vulnerableReason' => 'reason',
                'employmentStatusCode' => PaymentLookupField::byIdentifier(LookupField::EMPLOYMENT_STATUS)
                    ?->paymentLookupValues()
                    ->where('value', $this->payment->employment_status)
                    ->first()
                    ?->payment_provider_values['propensio'] ?? 'E',
                'employmentEmployerName' => $this->payment->employer_name ?? '',
                'employmentOccupation' => $this->payment->job_title ?? '',
                'employmentTimeYears' => $this->payment->time_with_employer ? (string) floor($this->payment->time_with_employer / 12) : '0',
                'employmentTimeMonths' => $this->payment->time_with_employer ? (string) ($this->payment->time_with_employer % 12) : '0'
            ],
            ...$addresses,
            'bankDetails' => [
                'bankSortCode' => str_replace('-', '', $this->payment->bank_account_sort_code),
                'bankAccountNumber' => $this->payment->bank_account_number,
                'bankAccountHolderName' => $this->payment->bank_account_holder_name,
                'bankName' => $this->payment->bank_name,
                'bankTimeYears' => $this->payment->time_with_employer ? (string) floor($this->payment->time_with_employer / 12) : '0',
                'bankTimeMonths' => $this->payment->time_with_employer ? (string) ($this->payment->time_with_employer % 12) : '0'
            ],
            'incomeExpenditure' => [
                'grossAnnualSalary' => $this->payment->gross_income_individual,
                'grossHouseholdIncome' => $this->payment->gross_income_household,
                'netMonthlySalary' => $this->payment->net_monthly_income_individual,
                'netMonthlyIncomeBenefits' => 0,
                'netMonthlyIncomePension' => 0,
                'netMonthlyIncomeOtherSources' => 0,
                'monthlyExpensesRentOrMortgage' => $this->payment->mortgage_monthly,
                'monthlyExpensesOther' => $this->payment->rent_monthly,
                'expectAnySignificantIncomeChg' => false,
                'expectAnySignificantIncomeChgReason' => 'reason'
            ]
        ];
    }

    public function get(): array
    {
        return $this->requestData;
    }
}
