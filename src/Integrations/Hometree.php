<?php

namespace Mralston\Payment\Integrations;

use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Mralston\Payment\Data\ErrorCollectionData;
use Mralston\Payment\Data\ErrorData;
use Mralston\Payment\Data\PrequalPromiseData;
use Mralston\Payment\Events\OffersReceived;
use Mralston\Payment\Events\OffersUpdated;
use Mralston\Payment\Events\PrequalError;
use Mralston\Payment\Interfaces\LeaseGateway;
use Mralston\Payment\Interfaces\ParsesErrors;
use Mralston\Payment\Interfaces\PaymentGateway;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Interfaces\PaymentParentModel;
use Mralston\Payment\Interfaces\PrequalifiesCustomer;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentLookupValue;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Models\PaymentProvider;
use Mralston\Payment\Models\PaymentSurvey;
use Throwable;

class Hometree implements PaymentGateway, LeaseGateway, PrequalifiesCustomer, ParsesErrors
{
    /**
     * Endpoints to be used based on environment.
     *
     * @var array|string[]
     *
     */
    private array $endpoints = [
        'local' => 'https://api.preprod.hometreefinance.dev/partner/v1.0',
        'dev' => 'https://api.preprod.hometreefinance.dev/partner/v1.0',
        'testing' => 'https://api.preprod.hometreefinance.dev/partner/v1.0',
        'production' => 'https://api.hometreefinance.co.uk/partner/v1.0',
    ];

    /**
     * API endpoint to send POST requests to.
     *
     * @var string
     */
    private string $endpoint;

    private $requestData = null;
    private $responseData = null;

    public function __construct(
        protected string $key,
        string $endpoint
    ) {
        $this->endpoint = $this->endpoints[$endpoint];
    }

    /**
     * Creates an application returns lease offers
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createApplication(
        PaymentSurvey $survey,
        float $totalCost,
        float $amount,
        float $deposit,
    ): array {
        $helper = app(PaymentHelper::class)
            ->setParentModel($survey->parentable);

        $firstCustomer = $survey->customers->first();
        $firstAddress = $survey->addresses->first();
        $previousAddress = $survey->addresses->get(1);

        $payload = [
            'customer' => [
                'first_name' => $firstCustomer['firstName'],
                ...(
                filled($firstCustomer['middleName']) ?
                    ['middle_name' => $firstCustomer['middleName']] :
                    [] // Omit the middle name field entirely if it's empty
                ),
                'last_name' => $firstCustomer['lastName'],
            ],
            'address' => [
                ...(!empty($firstAddress['udprn']) ? ['udprn' => $firstAddress['udprn']] : []),
                ...(!empty($firstAddress['uprn']) ? ['uprn' => $firstAddress['uprn']] : []),
            ],
            'order' => [
                'lines' => $helper->getBasketItems(),
                'details' => [
                    'immersion_diverter' => false,
                    'bird_blocker' => $helper->hasFeature('bird_blocker'),
                    'scaffold' => $helper->hasFeature('scaffold'),
                    'generation_month_12_kwh' => $this->stringifyDecimal($helper->getSem()),
                    'savings_month_12_solar_gross' => $this->stringifyDecimal($helper->getSolarSavingsYear1()),
                    'savings_month_12_ess_gross' => $this->stringifyDecimal($helper->getBatterySavingsYear1()),
                ],
                'price' => [
                    // TODO: If they ever levy VAT on solar panels again, we'll need to check this code is right
                    'net_value' => $this->stringifyDecimal($amount),
                    'vat' => $this->stringifyDecimal($helper->getVatRate()),
                    'vat_value' => $this->stringifyDecimal($helper->getVat()),
                ]
            ],
            ...(
                ($survey?->basic_questions_completed ?? false) ?
                    [
                        'applicants' => $survey->customers
                            ->map(function ($customer) use ($survey, $firstAddress, $previousAddress) {
                                return [
                                    'first_name' => $customer['firstName'],
                                    ...(
                                    filled($customer['middleName']) ?
                                        ['middle_name' => $customer['middleName']] :
                                        [] // Omit the middle name field entirely if it's empty
                                    ),
                                    'last_name' => $customer['lastName'],
                                    'email' => $customer['email'],
                                    'mobile_phone_number' => $customer['mobile'],
                                    'dob' => $customer['dateOfBirth'],
                                    'address' => [
                                        ...(!empty($firstAddress['udprn']) ? ['udprn' => $firstAddress['udprn']] : []),
                                        ...(!empty($firstAddress['uprn']) ? ['uprn' => $firstAddress['uprn']] : []),
                                    ],
                                    ...(
                                    $previousAddress ?
                                        [
                                            'previous_address' => [
                                                ...(!empty($previousAddress['udprn']) ? ['udprn' => $previousAddress['udprn']] : []),
                                                ...(!empty($previousAddress['uprn']) ? ['uprn' => $previousAddress['uprn']] : []),
                                            ]
                                        ] :
                                        []
                                    ),
                                    ...(
                                    $survey->basic_questions_completed ? [
                                        'affordability' => [
                                            'gross_annual_income' => $this->stringifyDecimal($customer['grossAnnualIncome']),
                                            'dependants' => $customer['dependants'],
                                            'employment_status' => PaymentLookupValue::byValue($customer['employmentStatus'])->payment_provider_values['hometree'],
                                        ]
                                    ] : []
                                    ),

                                ];
                            })->toArray(),
                    ] :
                    []
            ),
            'reference' => $helper->getReference() . '-' . Str::of(Str::random(5))->upper(),
        ];

        Log::debug('Hometree prequal request:', $payload);
        Log::debug('POST /applications');

        $response = Http::baseUrl($this->endpoint)
            ->withHeader('X-Client-App', config('payment.hometree.client_id', 'Hometree'))
            ->withToken($this->key, 'Token')
            ->post('/applications', $payload);

        $json = $response->json();

//        Log::debug('Hometree prequal response:', $json);

        $response->throw();

        return $json;
    }

    /**
     * Updates applicants on an application
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updateApplication(
        PaymentSurvey $survey,
        string $applicationId
    ): array {
        $firstAddress = $survey->addresses->first();
        $previousAddress = $survey->addresses->get(1);

        $payload = [
            'applicants' => $survey->customers
                ->map(function ($customer) use ($survey, $firstAddress, $previousAddress) {
                    return [
                        'first_name' => $customer['firstName'],
                        ...(
                        filled($customer['middleName']) ?
                            ['middle_name' => $customer['middleName']] :
                            [] // Omit the middle name field entirely if it's empty
                        ),
                        'last_name' => $customer['lastName'],
                        'email' => $customer['email'],
                        'mobile_phone_number' => $customer['mobile'],
                        'dob' => $customer['dateOfBirth'],
                        'address' => [
                            ...(!empty($firstAddress['udprn']) ? ['udprn' => $firstAddress['udprn']] : []),
                            ...(!empty($firstAddress['uprn']) ? ['uprn' => $firstAddress['uprn']] : []),
                        ],
                        ...(
                        $previousAddress ?
                            [
                                'previous_address' => [
                                    ...(!empty($previousAddress['udprn']) ? ['udprn' => $previousAddress['udprn']] : []),
                                    ...(!empty($previousAddress['uprn']) ? ['uprn' => $previousAddress['uprn']] : []),
                                ]
                            ] :
                            []
                        ),
                        ...(
                        $survey->basic_questions_completed ? [
                            'affordability' => [
                                'gross_annual_income' => $customer['grossAnnualIncome'],
                                'dependants' => $customer['dependants'],
                                'employment_status' => PaymentLookupValue::byValue($customer['employmentStatus'])->payment_provider_values['hometree'],
                            ]
                        ] : []
                        ),

                    ];
                })->toArray(),
        ];

//        Log::debug('Hometree update application request:', $payload);
        Log::debug('PATCH /applications/' . $applicationId);

        $response = Http::baseUrl($this->endpoint)
            ->withHeader('X-Client-App', config('payment.hometree.client_id', 'Hometree'))
            ->withToken($this->key, 'Token')
            ->patch('/applications/' . $applicationId, $payload);

        $json = $response->json();

        Log::debug('status: ' . $json['status']);

//        Log::debug('Hometree update application response:', $json);

        $response->throw();

        return $json;
    }

    public function getApplication(string $applicationId): array
    {
        Log::debug('GET /applications/' . $applicationId);

        $response = Http::baseUrl($this->endpoint)
            ->withHeader('X-Client-App', config('payment.hometree.client_id', 'Hometree'))
            ->withToken($this->key, 'Token')
            ->get('/applications/' . rawurlencode($applicationId))
            ->throw()
            ->json();

//        Log::debug('Hometree prequal update:', $response);

        Log::debug('status: ' . $response['status']);

        return $response;
    }

    public function getProducts(): array
    {
        try {
            $response = Http::baseUrl($this->endpoint)
                ->withHeader('X-Client-App', config('payment.hometree.client_id', 'Hometree'))
                ->withToken($this->key, 'Token')
                ->get('/products')
                ->throw()
                ->json();
        } catch (Throwable $ex) {
            Log::error('Failed to retrieve products from API.');
            Log::error('Error #' . $ex->getCode() . ': ' . $ex->getMessage());
            Log::error('URL: ' . $this->endpoint . '/products');
            throw $ex;
        }

        return $response;
    }

    public function prequal(PaymentSurvey $survey, float $totalCost): PrequalPromiseData
    {
        dispatch(function () use ($survey, $totalCost) {
            $helper = app(PaymentHelper::class)
                ->setParentModel($survey->parentable);

            $paymentProvider = PaymentProvider::byIdentifier('hometree');

            $deposit = $survey->lease_deposit;
            $amount = $totalCost - $deposit;

            // See if there are already offers
            $offers = $survey
                ->paymentOffers()
                ->where('payment_provider_id', $paymentProvider->id)
                ->where('total_cost', $totalCost)
                ->where('amount', $amount)
                ->where('deposit', $deposit)
                ->where('selected', false)
                ->get();

            // If there aren't any offers...
            if ($offers->isEmpty()) {
                try {
                    $response = $this->createApplication($survey, $totalCost, $amount, $deposit);

                    $offers = collect($response['offers'])
                        ->map(function ($offer) use ($survey, $paymentProvider, $response, $totalCost, $amount, $deposit) {
                            $productName = $paymentProvider->name . ' ' . $offer['name'] . ' ' . ($offer['params']['term'] / 12) . ' years' .
                                (
                                    $offer['params']['upfront_payment_gross'] > 0 ?
                                        ' (' . Number::currency($offer['params']['upfront_payment_gross'], 'GBP', precision: 0)  . ' up front)' :
                                        ''
                                );

                            // Create the product if it doesn't exist
                            $paymentProduct = $paymentProvider
                                ->paymentProducts()
                                ->firstOrCreate([
                                'identifier' => 'hometree_' . $offer['params']['term'] . ($offer['params']['upfront_payment_gross'] > 0 ? '_' . $offer['params']['upfront_payment_gross'] : ''),
                            ], [
                                'name' => $productName,
                                'term' => $offer['params']['term'],
                            ]);

                            return $this->upsertPaymentOffer($survey->parentable, [
                                'payment_survey_id' => $survey->id,
                                'payment_product_id' => $paymentProduct->id,
                                'payment_provider_id' => $paymentProvider->id,
                                'name' => $productName,
                                'type' => 'lease',
                                'reference' => $response['reference'],
                                'total_cost' => $totalCost,
                                'amount' => $amount,
                                'deposit' => $deposit,
                                'term' => $offer['params']['term'],
                                'priority' => $offer['rank'],
                                'upfront_payment' => $offer['params']['upfront_payment_gross'] ?? 0,
                                'first_payment' => $offer['params']['min_payments_gross'][0] ?? 0,
                                'monthly_payment' => $offer['params']['monthly_payment_gross'],
                                'final_payment' => $offer['params']['monthly_payment_gross'],
                                'minimum_payments' => $offer['params']['min_payments_gross'],
                                'total_payable' => collect($offer['params']['min_payments_gross'])->sum(),
                                'provider_application_id' => $response['id'],
                                'provider_offer_id' => $offer['id'],
                                'status' => $offer['status'],
                                'preapproval_id' => $offer['preapproval_id'],
                                'small_print' => $offer['params']['disclaimer'],
                            ]);
                        });

                    $this->pollForUpdates($survey, $response['id'], 3);

                } catch (RequestException $ex) {
                    event(new PrequalError(
                        gateway: static::class,
                        type: 'lease',
                        surveyId: $survey->id,
                        errorCode: $ex->getCode(),
                        errorMessage: $ex->getMessage(),
                        response: (string)$ex->response?->getBody(),
                    ));
                } catch (QueryException $ex) {
                    event(new PrequalError(
                        gateway: static::class,
                        type: 'lease',
                        surveyId: $survey->id,
                        errorCode: 500,
                        errorMessage: $ex->getMessage(),
                    ));
                } catch (Throwable $ex) {
                    event(new PrequalError(
                        gateway: static::class,
                        type: 'lease',
                        surveyId: $survey->id,
                        errorCode: $ex->getCode(),
                        errorMessage: $ex->getMessage(),
                    ));
                }
            }

            // Broadcast the offers
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

    protected function pollForUpdates(
        PaymentSurvey $survey,
        string $applicationId,
        int $every = 10,
        int $for = 60,
        ?int $delay = null
    ): void {
        $surveyId = $survey->id;

        $helper = app(PaymentHelper::class)
            ->setParentModel($survey->parentable);

        $amount = $helper->getTotalCost() - $survey->lease_deposit;

        $paymentProviderId = PaymentProvider::byIdentifier('hometree')->id;

        dispatch(function () use ($surveyId, $applicationId, $amount, $paymentProviderId, $every, $for) {
            $startTime = now();

            $survey = PaymentSurvey::find($surveyId);
            $parent = $survey->parentable;

            while (now()->diffInSeconds($startTime) < $for) {
                $response = $this->getApplication($applicationId);

                $offers = collect($response['offers'])
                    ->map(function ($offer) use ($response, $amount, $paymentProviderId, $surveyId, $parent) {
                        return $this->upsertPaymentOffer($parent, [
                            'payment_survey_id' => $surveyId,
                            'payment_provider_id' => $paymentProviderId,
                            'name' => $offer['name'] . ' ' . ($offer['params']['term'] / 12) . ' years'
                                . ($offer['params']['upfront_payment_gross'] > 0 ? ' (' . Number::currency($offer['params']['upfront_payment_gross'], 'GBP', precision: 0)  . ' up front)' : ''),
                            'type' => 'lease',
                            'amount' => $amount,
                            'term' => $offer['params']['term'],
                            'priority' => $offer['rank'],
                            'upfront_payment' => $offer['params']['upfront_payment_gross'] ?? 0,
                            'first_payment' => $offer['params']['min_payments_gross'][0] ?? 0,
                            'monthly_payment' => $offer['params']['monthly_payment_gross'],
                            'final_payment' => $offer['params']['monthly_payment_gross'],
                            'minimum_payments' => $offer['params']['min_payments_gross'],
                            'total_payable' => collect($offer['params']['min_payments_gross'])->sum(),
                            'provider_application_id' => $response['id'],
                            'provider_offer_id' => $offer['id'],
                            'status' => $offer['status'],
                            'preapproval_id' => $offer['preapproval_id'],
                            'small_print' => $offer['params']['disclaimer'],
                        ]);
                    })
                    ->reject(fn ($offer) => empty($offer));

                event(new OffersUpdated(
                    gateway: static::class,
                    surveyId: $surveyId,
                    offers: $offers,
                ));

                if ($response['status'] !== 'processing') {
                    break;
                }

                sleep($every);
            }

        })->delay(now()->addSeconds($delay ?? 0));
    }

    protected function upsertPaymentOffer(PaymentParentModel $parent, array $data): PaymentOffer
    {
        // TODO: Should be able to use updateOrCreate now the odder IDs are fixed in the API
        $paymentOffer = PaymentOffer::firstWhere('provider_offer_id', $data['provider_offer_id']);

        if ($paymentOffer) {
            $paymentOffer->update($data);
            return $paymentOffer;
        }

        return $parent
            ->paymentOffers()
            ->create($data);
    }

    public function apply(Payment $payment): array // TODO: Update Payment object with result & change return type to Payment
    {
        $offer = $payment->paymentOffer;

        $this->requestData = null;

        if (!empty($offer->preapproval_id)) {
            $this->requestData = ['preapproval_id' => $offer->preapproval_id];
        }

        Log::debug('POST /applications/' . $offer->provider_application_id . '/offers/' . $offer->provider_offer_id . '/select');

        $response = Http::baseUrl($this->endpoint)
            ->withHeader('X-Client-App', config('payment.hometree.client_id', 'Hometree'))
            ->withToken($this->key, 'Token')
            ->post('/applications/' . $offer->provider_application_id . '/offers/' . $offer->provider_offer_id . '/select', $this->requestData);

        // Add application and offer IDs to the request data stored to the DB for easier troubleshooting
        $this->requestData = [
            ...($this->requestData ?? []),
            'application_id' => $offer->provider_application_id,
            'offer_id' => $offer->provider_offer_id,
        ];

        $this->responseData = $response->json();

        Log::debug('status: ' . $this->responseData['status']);

//        Log::debug($this->requestData);
//        Log::debug($this->responseData);

        $response->throw();

        return $this->responseData;
    }

    public function getRequestData(): ?array
    {
        return $this->requestData;
    }

    public function getResponseData(): ?array
    {
        return $this->responseData;
    }

    public function cancel(Payment $payment): bool
    {
        $this->requestData = [
            'reason' => 'customer.unknown',
        ];

        Log::debug('POST /applications/' . $payment->provider_foreign_id . '/abandon');

        $response = Http::baseUrl($this->endpoint)
            ->withHeader('X-Client-App', config('payment.hometree.client_id', 'Hometree'))
            ->withToken($this->key, 'Token')
            ->post('/applications/' . $payment->provider_foreign_id . '/abandon', $this->requestData);

        // Add application and offer IDs to the request data stored to the DB for easier troubleshooting
        $this->requestData = [
            ...$this->requestData,
            'application_id' => $payment->provider_foreign_id,
        ];

        $this->responseData = $response->json();

//        Log::debug($this->requestData);
//        Log::debug($this->responseData);

        $response->throw();

        return true;
    }

    public function pollStatus(Payment $payment): array
    {
        return $this->getApplication($payment->provider_foreign_id);
    }

    public function parseErrors(Collection $response): ErrorCollectionData
    {
        return new ErrorCollectionData(
            $response
                ->map(function ($value, $key) {
                    return new ErrorData($key, $value[0]);
                })
        );
    }

    /**
     * Converts decimals to strings but leaves whole numbers as integers, as required by Hometree's API.
     *
     * @param int|float $number
     * @return int|string
     */
    private function stringifyDecimal(int|float $number): int|string
    {
        if (intval($number) == $number) {
            return intval($number);
        }

        return (string)$number;
    }

    public function healthCheck(): bool
    {
        try {
            $this->getProducts();
        } catch (Throwable $ex) {
            return false;
        }

        return true;
    }
}
