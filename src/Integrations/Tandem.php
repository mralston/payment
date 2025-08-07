<?php

namespace Mralston\Payment\Integrations;

use App\Address;
use App\FinanceApplication;
use App\Mail\FinanceApplicationCancelled;
use App\Mail\FinanceApplicationCancelManually;
use App\Quote;
use App\Settings;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Mralston\Payment\Data\PrequalData;
use Mralston\Payment\Data\PrequalPromiseData;
use Mralston\Payment\Events\OffersReceived;
use Mralston\Payment\Interfaces\FinanceGateway;
use Mralston\Payment\Interfaces\PaymentGateway;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Interfaces\PrequalifiesCustomer;
use Mralston\Payment\Models\PaymentProvider;
use Mralston\Payment\Models\PaymentSurvey;

class Tandem implements PaymentGateway, FinanceGateway, PrequalifiesCustomer
{
    /**
     * Endpoints to be used based on environment.
     *
     * @var array|string[]
     */
    private array $endpoints = [
        'local' => 'https://de-apiary-api.honeycombexternal.com:44310/acquisitionWebService/Retail/retail',
        'dev' => 'https://apim-01-ext.honeycombexternal.com/Retail/de',
        'testing' => 'https://apim-01-ext.honeycombexternal.com/Retail/te',
        'production' => 'https://apim-01-ext.honeycombexternal.com/Retail',
    ];

    /**
     * API KEY for authentication.
     *
     * @var string
     */
    private string $key;

    /**
     * API endpoint to send POST requests to.
     *
     * @var string
     */
    private string $endpoint;

    public function __construct(string $key, string $endpoint)
    {
        $this->key = $key;
        $this->endpoint = $this->endpoints[$endpoint];
    }

    /**
     * Checks whether the API is functional.
     *
     * @return bool
     */
    public function healthCheck()
    {
        return (Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->key
            ])
                ->get($this->endpoint . '/HealthCheck')
            ['results'][0]['error'] ?? null) == 'Success';
    }

    public function apply(FinanceApplication $application)
    {
        // Temporary workaround. Can be removed pretty sharpish
        if (empty($application->reference)) {
            $application->update([
                'reference' => $application->quote_id . '-' . $application->id
            ]);
        }

        $data = [
            'references' => [
                'externalUniqueReference' => $application->reference,
            ],
            'finance' => [
                'financeProductCode' => 'Standard', // to be provided by Allium?
                'advance' => $application->loan_amount,
                'termMonths' => $application->loan_term,
                'apr' => $application->apr,
                'deposit' => $application->deposit ?? 0,
                'depositTakenExternally' => true,
                // API expects the number of deferred payments, not the number of months deferred for.
                // 4 months deferred = 3 deferred payments. Therefore we subtract 1.
                'deferredPayments' => !empty($application->deferred_period) ? $application->deferred_period - 1 : 0,
            ],
            'applicants' => [
                'primaryApplicant' => [
                    'title' => $application->title,
                    'firstName' => $application->first_name,
                    'middleNames' => $application->middle_name,
                    'lastName' => $application->last_name,
                    'dateOfBirth' => optional($application->date_of_birth)->format('Y-m-d'),
                    'maritalStatus' => $this->convertMaritalStatus($application->marital_status),
                    'addresses' => [
                        'currentAddress' => $this->convertAddress($application->addresses->first()),
                        'previousAddresses' => $application->addresses
                            ->skip(1)
                            ->values()
                            ->map(function ($address) {
                                return $this->convertAddress($address);
                            })
                    ],
                    'nationality' => $application->british_citizen ? 'british' : 'non-british',
                    'residentialStatus' => $this->residentialStatus($application->homeowner_status, $application->has_mortgage),
                    'contactDetails' => [
                        'emailAddress' => $application->email_address,
                        'mobilePhone' => $application->mobile,
                        'homePhone' => $application->landline,
                    ],
                    'employment' => [
                        'employmentStatus' => $this->convertEmploymentStatus($application->employment_status),
                        'occupation' => $application->occupation,
                        'employers' => [
                            'currentEmployer' => [
                                'employerName' => $application->employer_name ?: $this->convertEmploymentStatus($application->employment_status),
                            ],
                        ]
                    ],
                    'income' => [
                        'grossAnnualIncome' => $application->gross_income_individual,
                        'otherIncomes' => [
                            [
                                'type' => 'Gross household income',
                                'amount' => $application->gross_income_household,
                            ]
                        ]
                    ],
                    //'vulnerableCustomer' => '',
                    'monthlyOutgoings' => [
                        'Mortgage' => $application->mortgage_monthly,
                        'Rent' => $application->rent_monthly,
                    ],
                    'bankAccount' => [
                        'accountNumber' => $application->bank_account_number,
                        'sortCode' => (string)Str::of($application->bank_account_sort_code)->replace('-', ''),
                        'accountHolderName' => $application->bank_account_holder_name
                    ],
                    'dependents' => $application->dependents_count
                ]
            ],
            'retail' => [
                'retailerName' => Settings::byName('company_full_name')->value ?? null,
                'subRetailerName' => Settings::byName('company_name')->value ?? null,
                'productLegalDescription' => $application->quote->products_description ?? null,
                'retailSource' => 'retailPortal',
                'goods' => [
                    [
                        'description' => $application->quote->products_description ?? 'Various products',
                        'typeCode' => 'RESOLP001',
                        'totalPrice' => $application->quote->gross,
                        'quantity' => 1
                    ],
                ]
            ],
            'control' => [
                //'returnUrl' => 'string', // Feature intended for a different Allium client
                'webhookUri' => route('finance_applications.webhook', $application->uuid),
                'actions' => [
                    'declined',
                    'pending',
                    'referred',
                    'conditional_accept',
                    'accepted',
                    'parked',
                    'snagged',
                    'parked',
                    'customer_cancelled',
                    'payout_requested',
                    'active',
                    'live',
                    'expired',
                ]
            ]
        ];

        Log::channel('finance')->info(json_encode($data, JSON_PRETTY_PRINT));

        $submitted_at = Carbon::now();

        try {
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->key
            ])
                ->post($this->endpoint . '/submitRetailApplication', $data)
                ->throw();
        } catch (\Throwable $ex) {
            Log::channel('finance')->error($ex);
            // TODO: Should we deal with the error responses here in order to return a unified response, or just let it bubble up?
            throw $ex;
        }

        $json = $response->json();

        Log::channel('finance')->info(json_encode($json, JSON_PRETTY_PRINT));

        $application->lender_application_id = $json['applicationId'];
        $application->status = $json['status'];
        $application->offer_expiration_date = $json['offerExpirationDate'];
        $application->lender_request_data = json_encode($data);
        $application->lender_response_data = json_encode($json);
        $application->submitted_at = $submitted_at;

        if (in_array($json['status'], ['declined', 'conditional_accept', 'accepted'])) {
            $application->decision_received_at = Carbon::now();
        }

        $application->save();

        return $application;
    }

    public function signingMethod(): string
    {
        return 'online';
    }

    /**
     * Retrieves the URL to the finance application signing page
     *
     * @param FinanceApplication $application
     * @param string|null $return_url
     * @return mixed
     * @throws RequestException
     */
    public function getSigningUrl(FinanceApplication $application, ?string $return_url = null)
    {
        $data = [
            'returnURL' => $return_url
        ];

        $response = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => $this->key
        ])
            ->post($this->endpoint . '/' . $application->lender_application_id . '/getApplicationSigningLink', $data)
            ->throw();

        $json = $response->json();
        Log::channel('finance')->debug($json['signingLink']);
        // Work around for dev API bug. Hopefully due to be fixed upstream
        $json['signingLink'] = str_replace('honeycombexternal.com', 'alliummoney.co.uk', $json['signingLink']);

        return $json['signingLink'];
    }

    /**
     * Fetches updated status, loan data and offer expiry from lender.
     *
     * @param FinanceApplication $application
     * @return array
     * @throws RequestException
     */
    public function pollStatus(FinanceApplication $application): array
    {
        // Poll the Allium API
        $response = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => $this->key
        ])
            ->get($this->endpoint . '/' . $application->lender_application_id . '/getApplicationStatus');

        // Look for 404 response (which means Allium don't have an application matching the specified ID)
        if ($response->status() == 404) {
            return [
                'status' => 'NotFound',
                'lender_response_data' => null,
                'offer_expiration_date' => null,
            ];
        } else {
            // Otherwise allow the Http client to throw any other error it might have encountered
            $response->throw();
        }

        // Decode the response
        $json = $response->json();

        // Return the data
        return [
            'status' => $json['status'],
            'lender_response_data' => $json['finance'],
            'offer_expiration_date' => $json['offerExpirationDate'],
        ];
    }

    public function convertAddress($address)
    {
        // Split out house number / name
        if (!empty($address['house_number'])) {
            if (Address::isHouseName($address['house_number'])) {
                $address['building_name'] = $address['house_number'];
            } else {
                $address['building_number'] = $address['house_number'];
            }

            $address1 = $address['street'];
            $address2 = $address['address1'];
            $address3 = $address['address2'];

            $address['address1'] = $address1;
            $address['address2'] = $address2;
            $address['address3'] = $address3;
        } elseif (preg_match('/^([0-9]+[a-z]?) (.*)/i', trim($address['address1']), $matches)) {
            $address['building_number'] = $matches[1];
            $address['address1'] = $matches[2];
        } elseif (preg_match('/^([0-9]+[a-z]?)$/i', trim($address['address1']), $matches)) {
            $address['building_number'] = $matches[1];
            $address['address1'] = $address['address2'];
            $address['address2'] = $address['address3'];
            $address['address3'] = null;
        } else {
            $address['building_name'] = $address['address1'];
            $address['address1'] = $address['address2'];
            $address['address2'] = $address['address3'];
            $address['address3'] = null;
        }

        return [
            'buildingName' => $address['building_name'] ?? null,
            'buildingNumber' => $address['building_number'] ?? null,
            'address1' => $address['address1'],
            'address2' => $address['address2'],
            'address3' => $address['address3'],
            'town' => $address['town'],
            'postcode' => $address['post_code'],
            'countryCode' => 'UK',
            'monthsAtAddress' => $address['time_at_address'] ?? 0
        ];
    }

    public function convertEmploymentStatus($employment_status)
    {
        switch ($employment_status) {
            case 'employed':
                return 'full_time_employed';
                break;
            case 'self-employed':
                return 'full_time_self_employed';
                break;
            case 'retired':
                return 'retired';
                break;
            case 'unemployed':
                return 'unemployed';
                break;
            default:
                return 'other';
                break;

        }
    }

    public function convertMaritalStatus($marital_status)
    {
        switch ($marital_status) {
            case 'cohabiting':
                return 'living_together';
                break;
            default:
                return $marital_status;
                break;

        }
    }

    private function productsDescription(?Quote $quote = null): ?string
    {
        if (empty($quote)) {
            return null;
        }

        $items = [];

        if ($quote->has_solar_panels) {
            $items[] = 'Solar Panels';
        }

        if ($quote->has_battery_storage) {
            $items[] = 'Battery Storage';
        }

        return collect($items)->implode(', ');
    }

    private function residentialStatus(string $homeowner_status, bool $has_mortgage)
    {
        if ($homeowner_status == 1 && $has_mortgage) {
            return 'homeowner_with_mortgage';
        } elseif ($homeowner_status == 1 && !$has_mortgage) {
            return 'homeowner_no_mortgage';
        } else {
            return 'tenant';
        }
    }

    public function cancel(FinanceApplication $application)
    {
        $data = [
            'cancellationReason' => 'Customer Withdrawn',
        ];

        try {
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->key
            ])
                ->post($this->endpoint . '/' . $application->lender_application_id . '/cancelApplication', $data)
                ->throw();

            // The underwriting team have asked to be e-mailed explicitly
            Mail::to($application->finance_lender->underwriter_email)
                ->send(new FinanceApplicationCancelled($application));
        } catch (RequestException $ex) {
            // Allium return a 403 if the loan has already been cancelled
            if ($ex->getCode() == 403) {
                Log::channel('finance')
                    ->debug('Cancellation request for ' . $application->reference . ' rejected (403)');

                // Poll the status of the application to see where it's genuinely up to
                $result = $this->pollStatus($application);

                Log::channel('finance')->debug('Application status: ' . $result['status']);

                // If it isn't 'expired' then e-mail them for manual cancellation
                if ($result['status'] != 'expired') {
                    Log::channel('finance')->debug('Sending cancellation request e-mail');
                    Mail::to($application->finance_lender->underwriter_email)
                        ->send(new FinanceApplicationCancelManually($application));
                } else {
                    Log::channel('finance')->debug('Application was already cancelled successfully');
                }

                return true;
            }

            // Otherwise re-throw the exception
            throw $ex;
        }

        return true;
    }

    public function sendSatNote(FinanceApplication $application)
    {
        $data = [
            'cancellationReason' => 'Customer Withdrawn',
        ];

        try {
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->key
            ])
                ->post($this->endpoint . '/' . $application->lender_application_id . '/notifyFulfilment', $data)
                ->throw();

            $json = $response->json();

            #Log::channel('finance')->debug(print_r($json, true));

            return $json['fulfilmentAccepted'];
        } catch (\Throwable $ex) {
            Log::channel('finance')->debug('Failed to send Sat Note to Allium for finance application #' . $application->id);
            Log::channel('finance')->debug('Error #' . $ex->getCode() . ': ' . $ex->getMessage());
            return false;
        }
    }

    public function uploadEpvsCertificate(FinanceApplication $application, string $encodedFile)
    {
        $data = [
            'file' => base64_encode($encodedFile)
        ];

        try {
            $url = $this->endpoint . '/' . $application->lender_application_id . '/uploadEPVSCertificate';

            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->key

            ])
                ->post($url, $data)
                ->throw();

            $json = $response->json();
            Log::info(print_r($json, true));

            return true;
        } catch (\Throwable $ex) {
            if ($ex->getCode() == 404) {
                Log::warning('Finance application #' . $application->id . ' not waiting for EPVS certificate.');
                return;
            }

            Log::error('Failed to upload certificate to Allium for finance application #' . $application->id);
            Log::error('Error #' . $ex->getCode() . ': ' . $ex->getMessage());
            Log::error('URL: ' . $url);
            return false;
        }
    }

    public function calculateRepayments(int $loanAmount, float $apr, int $loanTerm, ?int $deferredPeriod = null)
    {
        return Cache::remember(
            'calculateRepayments-' . $loanAmount . '-' . $apr . '-' . $loanTerm . '-' . $deferredPeriod,
            60 * 10,
            function () use ($loanAmount, $loanTerm, $apr, $deferredPeriod) {
                $data = [
                    'principal' => $loanAmount,
                    'termMonths' => $loanTerm,
                    'apr' => $apr,
                    // API expects the number of deferred payments, not the number of months deferred for.
                    // 4 months deferred = 3 deferred payments. Therefore we subtract 1.
                    'deferredPayments' => !empty($deferredPeriod) ? $deferredPeriod - 1 : 0,
                ];

                Log::info(print_r($data, true));

                try {
                    $url = $this->endpoint . '/financeCalculation';

                    $response = Http::withHeaders([
                        'Ocp-Apim-Subscription-Key' => $this->key

                    ])
                        ->get($url, $data);

                    $response->throw();

                    $json = $response->json();
                    Log::info(print_r($json, true));

                    return $json;
                } catch (\Throwable $ex) {
                    Log::error('Failed to retrieve repayments from API.');
                    Log::error('Error #' . $ex->getCode() . ': ' . $ex->getMessage());
                    Log::error('URL: ' . $url);
                    Log::error('Data: ' . print_r($data, true));
                    Log::error('Response: ' . $response->body());
                    throw $ex;
                }
            }
        );
    }

    public function calculateRepaymentsForApplication(FinanceApplication $financeApplication)
    {
        return $this->calculateRepayments(
            $financeApplication->loan_amount,
            $financeApplication->apr,
            $financeApplication->loan_term,
            $financeApplication->deferred_period ?? 0
        );
    }

    public function financeProducts(): Collection
    {
        try {
            $url = $this->endpoint . '/financeProducts';

            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->key

            ])
                ->get($url)
                ->throw();

            $json = $response->json();

            return collect($json['financeProducts'] ?? []);
        } catch (\Throwable $ex) {
            Log::error('Failed to retrieve repayments from API.');
            Log::error('Error #' . $ex->getCode() . ': ' . $ex->getMessage());
            Log::error('URL: ' . $url);
//            Log::error('Data: ' . print_r($data, true));
            throw $ex;
        }
    }

    public function prequal(PaymentSurvey $survey): PrequalPromiseData|PrequalData
    {
        dispatch(function () use ($survey) {
            $helper = app(PaymentHelper::class)
                ->setParentModel($survey->parentable);

            $amount = $helper->getTotalCost() - $helper->getDeposit();

            $paymentProvider = PaymentProvider::byIdentifier('tandem');

            // See if there are already offers
            $offers = $survey
                ->paymentOffers()
                ->where('payment_provider_id', $paymentProvider->id)
                ->where('amount', $amount)
                ->get();


            // If there aren't any offers...
            if ($offers->isEmpty()) {

                $products = $this->financeProducts();

                //Log::info(print_r($products, true));

                $offers = $products->map(function ($product) use ($survey, $paymentProvider, $amount) {

                    // Fetch repayments
                    try {
                        $repayments = $this->calculateRepayments(
                            loanAmount: $amount,
                            // Fake APR in local development as the testing API doesn't have the right rates
                            apr: app()->environment('local') ? 11.9 : $product['apr'],
                            loanTerm: $product['termMonths'],
                            deferredPeriod: $product['deferredPayments']
                        );
                    } catch (RequestException $ex) {
                        // Tandem's testing API often fails because it uses the live LMS
                        // with rates from the testing environment and they don't always match up
                        $repayments = [
                            'FinancialDetails' => [
                                'LoanAmount' => $amount,
                                'APR' => $product['apr'],
                                'TermMonths' => $product['termMonths'],
                                'DeferredPayments' => $product['deferredPayments'],
                                'DailyInterest' => 0,
                                'InterestRate' => 0,
                                'TotalCostOfCredit' => 0,
                                'TotalPayable' => 0,
                            ],
                            'RepaymentDetails' => [
                                'MonthlyRepayment' => 0,
                                'FirstRepaymentAmount' => 0,
                                'FinalRepaymentAmount' => 0,
                            ]
                        ];
                    }

                    Log::debug(print_r($repayments, true));

                    return $survey->paymentOffers()
                        ->create([
                            'name' => $paymentProvider->name . ' ' . $product['apr'] . '%' .
                                ($product['deferredPayments'] > 0 ? ' ' . $product['deferredPayments'] . ' months deferred' : ''),
                            'type' => 'finance',
                            'amount' => $amount,
                            'payment_provider_id' => $paymentProvider->id,
                            'apr' => $product['apr'],
                            'term' => $product['termMonths'],
                            'deferred' => $product['deferredPayments'],
                            'first_payment' => $repayments['RepaymentDetails']['FirstRepaymentAmount'],
                            'monthly_payment' => $repayments['RepaymentDetails']['MonthlyRepayment'],
                            'final_payment' => $repayments['RepaymentDetails']['FinalRepaymentAmount'],
                            'status' => 'final',
                        ]);
                });
            }

            event(new OffersReceived(
                gateway: static::class,
                surveyId: $survey->id,
                offers: $offers,
            ));
        });

        return new PrequalPromiseData(
            gateway: static::class,
            surveyId: $survey->id,
        );
    }
}
