<?php

namespace Mralston\Payment\Integrations;

use App\Address;
use App\Mail\FinanceApplicationCancelled;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Mralston\Payment\Data\AddressData;
use Mralston\Payment\Data\ErrorCollectionData;
use Mralston\Payment\Data\ErrorData;
use Mralston\Payment\Data\PrequalData;
use Mralston\Payment\Data\PrequalPromiseData;
use Mralston\Payment\Events\OffersReceived;
use Mralston\Payment\Interfaces\FinanceGateway;
use Mralston\Payment\Interfaces\ParsesErrors;
use Mralston\Payment\Interfaces\PaymentGateway;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Interfaces\PrequalifiesCustomer;
use Mralston\Payment\Interfaces\Signable;
use Mralston\Payment\Interfaces\WantsEpvs;
use Mralston\Payment\Interfaces\WantsSatNote;
use Mralston\Payment\Mail\CancelManually;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Models\PaymentProvider;
use Mralston\Payment\Models\PaymentStatus;
use Mralston\Payment\Models\PaymentSurvey;

class Tandem implements PaymentGateway, FinanceGateway, PrequalifiesCustomer, Signable, WantsSatNote, WantsEpvs, ParsesErrors
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

    private string $endpoint;

    private $requestData = null;
    private $responseData = null;

    public function __construct(
        protected string $key,
        string $endpoint
    ) {
        $this->endpoint = $this->endpoints[$endpoint];
    }

    public function getRequestData(): ?array
    {
        return $this->requestData;
    }

    public function getResponseData(): ?array
    {
        return $this->responseData;
    }

    /**
     * Checks whether the API is functional.
     *
     * @return bool
     */
    public function healthCheck(): bool
    {
        $this->requestData = null;

        $response = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => $this->key
        ])
        ->get($this->endpoint . '/HealthCheck');

        $this->responseData = $response->json();

        $response->throw();

        return ($this->responseData['results'][0]['error'] ?? null) == 'Success';
    }

    public function apply(Payment $payment): Payment
    {
        $helper = app(PaymentHelper::class)
            ->setParentModel($payment->parentable);

        $companyDetails = $helper->getCompanyDetails();

        $currentAddress = AddressData::from($payment->addresses->first());

        $this->requestData = [
            'references' => [
                'externalUniqueReference' => $payment->reference,
            ],
            'finance' => [
                'financeProductCode' => 'Standard', // Should this be populated with the value from /financeProducts ?
                'advance' => $payment->amount,
                'termMonths' => $payment->term,
                'apr' => $payment->apr,
                'deposit' => $payment->deposit ?? 0,
                'depositTakenExternally' => true,
                'deferredPayments' => intval($payment->deferred),
            ],
            'applicants' => [
                'primaryApplicant' => [
                    'title' => $payment->title,
                    'firstName' => $payment->first_name,
                    'middleNames' => $payment->middle_name,
                    'lastName' => $payment->last_name,
                    'dateOfBirth' => optional($payment->date_of_birth)->format('Y-m-d'),
                    'maritalStatus' => $payment->maritalStatus?->payment_provider_values['tandem'] ?? null,
                    'addresses' => [
                        'currentAddress' => [
                            'buildingName' => null,
                            'buildingNumber' => $currentAddress->houseNumber,
                            'address1' => $currentAddress->street,
                            'address2' => $currentAddress->address1,
                            'address3' => $currentAddress->address2,
                            'town' => $currentAddress->town,
                            'postcode' => $currentAddress->postCode,
                            'countryCode' => 'UK',
                            'monthsAtAddress' => floor(Carbon::parse($currentAddress->dateMovedIn . ' 00:00:00')->diffInMonths())
                        ],
                        'previousAddresses' => $payment->addresses
                            ->skip(1)
                            ->values()
                            ->map(function ($address) {
                                $address = AddressData::from($address);
                                return [
                                    'buildingName' => null,
                                    'buildingNumber' => $address->houseNumber,
                                    'address1' => $address->street,
                                    'address2' => $address->address1,
                                    'address3' => $address->address2,
                                    'town' => $address->town,
                                    'postcode' => $address->postCode,
                                    'countryCode' => 'UK',
                                    'monthsAtAddress' => floor(Carbon::parse($address->dateMovedIn . ' 00:00:00')->diffInMonths())
                                ];
                            })
                    ],
                    'nationality' => $payment->nationalityValue?->payment_provider_values['tandem'] ?? null,
                    'residentialStatus' => $payment->residentialStatus?->payment_provider_values['tandem'] ?? null,
                    'contactDetails' => [
                        'emailAddress' => $payment->email_address,
                        'mobilePhone' => $payment->mobile,
                        'homePhone' => $payment->landline,
                    ],
                    'employment' => [
                        'employmentStatus' => $payment->employmentStatus?->payment_provider_values['tandem'] ?? null,
                        'occupation' => $payment->occupation,
                        'employers' => [
                            'currentEmployer' => [
                                'employerName' => $payment->employer_name ?: $payment->employmentStatus?->payment_provider_values['tandem'] ?? null,
                            ],
                        ]
                    ],
                    'income' => [
                        'grossAnnualIncome' => $payment->gross_income_individual,
                        'otherIncomes' => [
                            [
                                'type' => 'Gross household income',
                                'amount' => $payment->gross_income_household,
                            ]
                        ]
                    ],
                    //'vulnerableCustomer' => '',
                    'monthlyOutgoings' => [
                        'Mortgage' => $payment->mortgage_monthly,
                        'Rent' => $payment->rent_monthly,
                    ],
                    'bankAccount' => [
                        'accountNumber' => $payment->bank_account_number,
                        'sortCode' => (string)Str::of($payment->bank_account_sort_code)->replace('-', ''),
                        'accountHolderName' => $payment->bank_account_holder_name
                    ],
                    'dependents' => $payment->dependants
                ]
            ],
            'retail' => [
                'retailerName' => $companyDetails->legalName,
                'subRetailerName' => $companyDetails->commonName,
                'productLegalDescription' => $payment->parentable->products_description ?? null, // TODO: Should we let the helper do this?
                'retailSource' => 'retailPortal',
                'goods' => [
                    [
                        'description' => $payment->parentable->products_description ?? 'Various products',
                        'typeCode' => 'RESOLP001',
                        'totalPrice' => $helper->getGross(),
                        'quantity' => 1
                    ],
                ]
            ],
            'control' => [
                'webhookUri' => route('payment.webhook.tandem', $payment->uuid),
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

        Log::info('tandem request', $this->requestData);

        $submitted_at = Carbon::now();

        try {
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->key
            ])
            ->post(
                $this->endpoint . '/submitRetailApplication',
                $this->requestData
            );

            $this->responseData = $response->json();

            $response->throw();

        } catch (\Throwable $ex) {
            $this->responseData = json_decode($ex->response->body(), true);

            Log::debug('Tandem response: ' . print_r($this->responseData, true));

            Log::channel('finance')->error($ex->getMessage(), $this->responseData);

            $paymentStatus = PaymentStatus::byIdentifier('error');
            $payment->update([
                'payment_status_id' => $paymentStatus?->id,
                'provider_request_data' => $this->requestData,
                'provider_response_data' => $this->responseData,
            ]);

            return $payment;
        }

        Log::channel('finance')->info('tandem response', $this->responseData);

        $paymentStatus = PaymentStatus::byIdentifier($this->responseData['status']);
        $payment->update([
            'provider_foreign_id' => $this->responseData['applicationId'],
            'payment_status_id' => $paymentStatus?->id,
            'offer_expiration_date' => $this->responseData['offerExpirationDate'],
            'provider_request_data' => $this->requestData,
            'provider_response_data' => $this->responseData,
            'submitted_at' => $submitted_at,
            ...(
                $paymentStatus->decided ?
                    ['decision_received_at' => Carbon::now()] :
                    []
            ),
            ...(
            $paymentStatus->referred ?
                ['was_referred' => true] :
                []
            ),
        ]);

        return $payment;
    }

    public function signingMethod(): string
    {
        return 'online';
    }

    public function getSigningUrl(Payment $payment): string
    {
        $response = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => $this->key
        ])
            ->post($this->endpoint . '/' . $payment->provider_foreign_id . '/getApplicationSigningLink', [])
            ->throw();

        $json = $response->json();
        // Work around for dev API bug. Hopefully due to be fixed upstream
        $json['signingLink'] = str_replace('honeycombexternal.com', 'alliummoney.co.uk', $json['signingLink']);

        return $json['signingLink'];
    }

    /**
     * Fetches updated status, loan data and offer expiry from lender.
     *
     * @param Payment $payment
     * @return array
     * @throws RequestException
     */
    public function pollStatus(Payment $payment): array
    {
        // Poll the Allium API
        $response = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => $this->key
        ])
            ->get($this->endpoint . '/' . $payment->provider_foreign_id . '/getApplicationStatus');

        dump($this->endpoint . '/' . $payment->provider_foreign_id . '/getApplicationStatus');

        // Look for 404 response (which means Allium don't have an application matching the specified ID)
        if ($response->status() == 404) {

            dump($response->body());

            $payment->update([
                'payment_status_id' => PaymentStatus::byIdentifier('NotFound')?->id,
            ]);

            return [
                'status' => 'NotFound',
                'lender_response_data' => null,
                'offer_expiration_date' => null,
            ];
        } else {
            // Otherwise allow the Http client to throw any other error it might have encountered
            $response->throw();
        }

        dump($response->body());

        // Decode the response
        $json = $response->json();

        $payment->update([
            'payment_status_id' => PaymentStatus::byIdentifier($json['status'])?->id,
        ]);

        // Return the data
        return [
            'status' => $json['status'],
            'lender_response_data' => $json['finance'],
            'offer_expiration_date' => $json['offerExpirationDate'],
        ];
    }

    public function cancel(Payment $payment, ?string $reason = null): bool
    {
        $data = [
            'cancellationReason' => 'Customer Withdrawn',
        ];

        try {
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->key
            ])
                ->post($this->endpoint . '/' . $payment->provider_foreign_id . '/cancelApplication', $data)
                ->throw();

            // The underwriting team have asked to be e-mailed explicitly
            Mail::to($payment->paymentProvider->underwriter_email)
                ->send(new FinanceApplicationCancelled($payment));
        } catch (RequestException $ex) {
            // Allium return a 403 if the loan has already been cancelled
            if ($ex->getCode() == 403) {
                Log::channel('finance')
                    ->debug('Cancellation request for ' . $payment->reference . ' rejected (403)');

                // Poll the status of the application to see where it's genuinely up to
                $result = $this->pollStatus($payment);

                Log::channel('finance')->debug('Application status: ' . $result['status']);

                // If it isn't 'expired' then e-mail them for manual cancellation
                if ($result['status'] != 'expired') {
                    Log::channel('finance')->debug('Sending cancellation request e-mail');
                    Mail::to($payment->paymentProvider->underwriter_email)
                        ->send(new CancelManually($payment));
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

    public function sendSatNote(Payment $payment): bool
    {
        $data = [
            'cancellationReason' => 'Customer Withdrawn',
        ];

        try {
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->key
            ])
                ->post($this->endpoint . '/' . $payment->provider_foreign_id . '/notifyFulfilment', $data)
                ->throw();

            $json = $response->json();

            #Log::channel('finance')->debug(print_r($json, true));

            return $json['fulfilmentAccepted'];
        } catch (\Throwable $ex) {
            Log::channel('finance')->debug('Failed to send Sat Note to Tandem for finance application #' . $payment->id);
            Log::channel('finance')->debug('Error #' . $ex->getCode() . ': ' . $ex->getMessage());
            return false;
        }
    }

    public function uploadEpvsCertificate(Payment $payment, string $encodedFile): bool
    {
        $data = [
            'file' => base64_encode($encodedFile)
        ];

        try {
            $url = $this->endpoint . '/' . $payment->provider_foreign_id . '/uploadEPVSCertificate';

            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->key

            ])
                ->post($url, $data)
                ->throw();

            $json = $response->json();
//            Log::channel('finance')->info(print_r($json, true));

            return true;
        } catch (\Throwable $ex) {
            if ($ex->getCode() == 404) {
                Log::channel('finance')->warning('Payment #' . $payment->id . ' not waiting for EPVS certificate.');
                return true; // Is this the right response?
            }

            Log::channel('finance')->error('Failed to upload certificate to Tandem for finance application #' . $payment->id);
            Log::channel('finance')->error('Error #' . $ex->getCode() . ': ' . $ex->getMessage());
            Log::channel('finance')->error('URL: ' . $url);
            return false;
        }
    }

    public function calculatePayments(int $loanAmount, float $apr, int $loanTerm, ?int $deferredPeriod = null): array
    {
        return Cache::remember(
            'calculatePayments-' . $loanAmount . '-' . $apr . '-' . $loanTerm . '-' . $deferredPeriod,
            60 * 10,
            function () use ($loanAmount, $loanTerm, $apr, $deferredPeriod) {
                $data = [
                    'principal' => $loanAmount,
                    'termMonths' => $loanTerm,
                    'apr' => $apr,
                    'deferredPayments' => intval($deferredPeriod),
                ];

//                Log::channel('finance')->info(print_r($data, true));

                try {
                    $url = $this->endpoint . '/financeCalculation';

                    $response = Http::withHeaders([
                        'Ocp-Apim-Subscription-Key' => $this->key

                    ])
                        ->get($url, $data);

                    $response->throw();

                    $json = $response->json();
//                    Log::channel('finance')->info(print_r($json, true));

                    return $json;
                } catch (\Throwable $ex) {
                    Log::channel('finance')->error('Failed to retrieve payments from API.');
                    Log::channel('finance')->error('Error #' . $ex->getCode() . ': ' . $ex->getMessage());
                    Log::channel('finance')->error('URL: ' . $url);
                    Log::channel('finance')->error('Data: ' . print_r($data, true));
                    Log::channel('finance')->error('Response: ' . $response->body());
                    throw $ex;
                }
            }
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
            dump($response->body());

            return collect($json['financeProducts'] ?? []);
        } catch (\Throwable $ex) {
            Log::channel('finance')->error('Failed to retrieve payments from API.');
            Log::channel('finance')->error('Error #' . $ex->getCode() . ': ' . $ex->getMessage());
            Log::channel('finance')->error('URL: ' . $url);
//            Log::channel('finance')->error('Data: ' . print_r($data, true));
            throw $ex;
        }
    }

    public function prequal(PaymentSurvey $survey, float $totalCost): PrequalPromiseData|PrequalData
    {
        dispatch(function () use ($survey, $totalCost) {
            $helper = app(PaymentHelper::class)
                ->setParentModel($survey->parentable);

            $paymentProvider = PaymentProvider::byIdentifier('tandem');

            $deposit = $survey->finance_deposit;
            $amount = $totalCost - $deposit;

            // See if there are already offers
            $offers = $survey
                ->paymentOffers()
                ->where('payment_provider_id', $paymentProvider->id)
                ->where('total_cost', $totalCost)
                ->where('amount', $amount)
                ->where('deposit', $deposit)
                ->where('monthly_payment', '>', 0)
                ->where('selected', false);

            $offers = $offers->get();

            // If there aren't any offers...
            if ($offers->isEmpty()) {

                $products = $this->financeProducts();

                $reference = $helper->getReference() . '-' . Str::of(Str::random(5))->upper();

                //Log::channel('finance')->info(print_r($products, true));

                $offers = $products->map(function ($product) use ($survey, $paymentProvider, $reference, $totalCost, $amount, $deposit) {
                    // Fetch payments
                    try {
                        $payments = $this->calculatePayments(
                            loanAmount: $amount,
                            apr: $product['apr'],
                            loanTerm: $product['termMonths'],
                            deferredPeriod: $product['deferredPayments']
                        );
                    } catch (RequestException $ex) {
                        // Tandem's testing API often fails because it uses the live LMS
                        // with rates from the testing environment and they don't always match up.
                        // Here we return a default (empty) result so the code can move on.
                        $payments = [
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

                    // If there are no payments, skip it
                    if ($payments['RepaymentDetails']['MonthlyRepayment'] <= 0) {
                        Log::channel('finance')->debug('No payment calc for product', $product);
                        return null;
                    }

                    $productName = $paymentProvider->name . ' ' .
                        ($product['apr'] > 0 ? $product['apr'] . '% ' : '') .
                        ($product['termMonths'] / 12) . ' year' . ($product['termMonths'] / 12 == 1 ? '' : 's') .
                        (intval($product['apr']) === 0 ? ' interest free' : '') .
                        ($product['deferredPayments'] > 0 ? ', ' . ($product['deferredPayments'] + 1) . ' months deferred' : ''); // +1 because Tandem quote deferred payments, not deferred months

//                    Log::channel('finance')->debug('Creating Tandem Product', [
//                        'payment_provider_id' => $paymentProvider->id,
//                        'identifier' => 'tandem_' . $product['apr'] . '_' . $product['termMonths'] . ($product['deferredPayments'] > 0 ? '+' . $product['deferredPayments'] : ''),
//                        'name' => $productName,
//                        'apr' => $product['apr'],
//                        'term' => $product['termMonths'],
//                        'deferred' => $product['deferredPayments'] > 0 ? $product['deferredPayments'] : null,
//                    ]);

                    // Create the product if it doesn't exist
                    $paymentProduct = $paymentProvider
                        ->paymentProducts()
                        ->withTrashed()
                        ->firstOrCreate([
                            'identifier' => 'tandem_' . $product['apr'] . '_' . $product['termMonths'] . ($product['deferredPayments'] > 0 ? '+' . $product['deferredPayments'] : ''),
                        ], [
                            'name' => $productName,
                            'apr' => $product['apr'],
                            'term' => $product['termMonths'],
                            'deferred' => $product['deferredPayments'] > 0 ? $product['deferredPayments'] : null,
                            'deferred_type' => $product['deferredPayments'] > 0 ? 'payments' : null,
                        ]);

                    // If the product has been soft deleted, don't store the offer
                    // This allows us to disable products we don't want to offer to customers
                    if ($paymentProduct->trashed()) {
                        Log::channel('finance')->debug('Tandem product soft deleted', $product);
                        return null;
                    }

                    // Create the offer
                    return $survey->parentable
                        ->paymentOffers()
                        ->create([
                            'payment_survey_id' => $survey->id,
                            'payment_product_id' => $paymentProduct->id,
                            'payment_provider_id' => $paymentProvider->id,
                            'name' => $productName,
                            'type' => 'finance',
                            'reference' => $reference,
                            'total_cost' => $totalCost,
                            'amount' => $amount,
                            'deposit' => $deposit,
                            'apr' => $product['apr'],
                            'term' => $product['termMonths'],
                            'deferred' => $product['deferredPayments'],
                            'deferred_type' => $product['deferredPayments'] > 0 ? 'payments' : null,
                            'first_payment' => $payments['RepaymentDetails']['FirstRepaymentAmount'],
                            'monthly_payment' => $payments['RepaymentDetails']['MonthlyRepayment'],
                            'final_payment' => $payments['RepaymentDetails']['FinalRepaymentAmount'],
                            'total_payable' => $payments['FinancialDetails']['TotalPayable'],
                            'status' => 'final',
                        ]);
                })
                ->reject(fn ($offer) => is_null($offer));
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

    public function parseErrors(Collection $response): ErrorCollectionData
    {
        Log::debug('errors to parse', $response->toArray());

        return new ErrorCollectionData(
            collect($response['errors'])
                ->map(function ($value, $key) {
                    return new ErrorData($key, $value[0]);
                })
        );
    }

    public function cancelOffer(PaymentOffer $paymentOffer): void
    {
        // Stub to satisfy interface, no action required.
    }
}
