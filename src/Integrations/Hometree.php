<?php

namespace Mralston\Payment\Integrations;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Mralston\Payment\Data\PrequalPromiseData;
use Mralston\Payment\Data\Offers;
use Mralston\Payment\Events\OffersReceived;
use Mralston\Payment\Events\OffersUpdated;
use Mralston\Payment\Events\PrequalError;
use Mralston\Payment\Interfaces\LeaseGateway;
use Mralston\Payment\Interfaces\PaymentGateway;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Interfaces\PrequalifiesCustomer;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Models\PaymentProvider;
use Mralston\Payment\Models\PaymentSurvey;

class Hometree implements PaymentGateway, LeaseGateway, PrequalifiesCustomer
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
        'production' => '',
    ];

    /**
     * API endpoint to send POST requests to.
     *
     * @var string
     */
    private string $endpoint;

    public function __construct(
        private string $key,
        string $endpoint,
    ) {
        $this->endpoint = $this->endpoints[$endpoint];
    }

    public function createApplication(
        PaymentSurvey $survey,
    ): array {
        $helper = app(PaymentHelper::class)
            ->setParentModel($survey->parentable);

        $firstCustomer = $survey->customers->first();
        $firstAddress = $survey->addresses->first();
        $previousAddress = $survey->addresses->get(1);

        $payload = [
            'customer' => [
                'first_name' => $firstCustomer['firstName'],
                'middle_name' => $firstCustomer['middleName'] ?? '',
                'last_name' => $firstCustomer['lastName'],
            ],
            'address' => [
                'udprn' => $firstAddress['udprn'],
            ],
            'order' => [
                'lines' => $helper->getBasketItems(),
                'details' => [
                    'immersion_diverter' => false,
                    'bird_blocker' => $helper->hasFeature('bird_blocker'),
                    'scaffold' => $helper->hasFeature('scaffold'),
                    'generation_month_12_kwh' => $helper->getSem(),
                    'savings_month_12_solar_gross' => $helper->getSolarSavingsYear1(),
                    'savings_month_12_ess_gross' => $helper->getBatterySavingsYear1(),
                ],
                'price' => [
                    'net_value' => $helper->getNet(),
                    'vat' => $helper->getVatRate(),
                    'vat_value' => $helper->getVat(),
                ]
            ],
            'applicants' => $survey->customers
                ->map(function ($customer) use ($survey, $firstAddress, $previousAddress) {
                    return [
                        'first_name' => $customer['firstName'],
                        'middle_name' => $customer['middleName'] ?? '',
                        'last_name' => $customer['lastName'],
                        'email' => $customer['email'],
                        'mobile_phone_number' => $customer['phone'],
                        'dob' => $customer['dateOfBirth'],
                        'address' => [
                            'udprn' => $firstAddress['udprn'],
                        ],
                        ...(
                            $previousAddress ?
                                [
                                    'previous_address' => [
                                        'udprn' => $previousAddress['udprn']
                                    ]
                                ] :
                                []
                        ),
                        'affordability' => [
                            'gross_annual_income' => $customer['grossAnnualIncome'],
                            'dependants' => $customer['dependants'],
                            'employment_status' => $customer['employmentStatus'],
                        ]
                    ];
                }),
            'reference' => $helper->getReference() . '-' . Str::of(Str::random(5))->upper(),
        ];

        $response = Http::baseUrl($this->endpoint)
            ->withHeader('X-Client-App', config('payment.hometree.client_id', 'Hometree'))
            ->withToken($this->key, 'Token')
            ->post('/applications', $payload)
            ->throw()
            ->json();

        return $response;
    }

    public function getApplication(string $applicationId): array
    {
        $response = Http::baseUrl($this->endpoint)
            ->withHeader('X-Client-App', config('payment.hometree.client_id', 'Hometree'))
            ->withToken($this->key, 'Token')
            ->get('/applications/' . rawurlencode($applicationId))
            ->throw()
            ->json();

        return $response;
    }

    public function getProducts(): array
    {
        try {
            $response = Http::baseUrl($this->endpoint)
                ->withToken($this->key)
                ->get('/products')
                ->throw()
                ->json();
        } catch (\Throwable $ex) {
            Log::error('Failed to retrieve products from API.');
            Log::error('Error #' . $ex->getCode() . ': ' . $ex->getMessage());
            Log::error('URL: ' . $this->endpoint . '/products');
            throw $ex;
        }

        return $response;
    }

    public function prequal(PaymentSurvey $survey): PrequalPromiseData
    {
        dispatch(function () use ($survey) {
            $helper = app(PaymentHelper::class)
                ->setParentModel($survey->parentable);

            $amount = $helper->getTotalCost() - $helper->getDeposit();

            $paymentProvider = PaymentProvider::byIdentifier('hometree');

            // See if there are already offers
            $offers = $survey
                ->paymentOffers()
                ->where('payment_provider_id', $paymentProvider->id)
                ->where('amount', $amount)
                ->get();

            // If there aren't any offers...
            if ($offers->isEmpty()) {
                Log::debug('No stored offers found for Hometree. Querying API...');
                try {
                    $response = $this->createApplication($survey);

                    $offers = collect($response['offers'])
                        ->map(function ($offer) use ($survey, $paymentProvider, $amount, $response) {
                            return $this->upsertPaymentOffer([
                                'payment_survey_id' => $survey->id,
                                'name' => $offer['name'] . ' ' . ($offer['params']['term'] / 12) . ' years'
                                    . ($offer['params']['upfront_payment_gross'] > 0 ? ' (' . Number::currency($offer['params']['upfront_payment_gross'], 'GBP', precision: 0)  . ' up front)' : ''),
                                'type' => 'lease',
                                'amount' => $amount,
                                'payment_provider_id' => $paymentProvider->id,
                                'term' => $offer['params']['term'],
                                'priority' => $offer['rank'],
                                'upfront_payment' => $offer['params']['upfront_payment_gross'] ?? 0,
                                'first_payment' => $offer['params']['min_payments_gross'][0] ?? 0,
                                'monthly_payment' => $offer['params']['monthly_payment_gross'],
                                'final_payment' => $offer['params']['monthly_payment_gross'],
                                'minimum_payments' => $offer['params']['min_payments_gross'],
                                'total_repayable' => collect($offer['params']['min_payments_gross'])->sum(),
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
                } catch (\Throwable $ex) {
                    event(new PrequalError(
                        gateway: static::class,
                        type: 'lease',
                        surveyId: $survey->id,
                        errorCode: $ex->getCode(),
                        errorMessage: $ex->getMessage(),
                    ));
                }
            }

//            Log::debug('offers size:', [strlen($offers->toJson())]);

//            Log::debug('offers:', $offers->toArray());

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

        Log::debug('Will poll for Hometree updates...', [$survey->id, $applicationId, $every, $for, $delay]);

        $surveyId = $survey->id;

        $helper = app(PaymentHelper::class)
            ->setParentModel($survey->parentable);

        $amount = $helper->getTotalCost() - $helper->getDeposit();

        $paymentProviderId = PaymentProvider::byIdentifier('hometree')->id;

        dispatch(function () use ($surveyId, $applicationId, $amount, $paymentProviderId, $every, $for) {

            Log::debug('Polling for Hometree updates...', [$surveyId, $applicationId, $every, $for]);

            $startTime = now();

            while (now()->diffInSeconds($startTime) < $for) {
                $response = $this->getApplication($applicationId);
//                Log::debug('Hometree poll response:', collect($response)->except(['offers.params.min_payments_gross', 'offers.params.disclaimer'])->toArray());

                $offers = collect($response['offers'])
                    ->map(function ($offer) use ($response, $amount, $paymentProviderId, $surveyId) {
                        return $this->upsertPaymentOffer([
                            'payment_survey_id' => $surveyId,
                            'name' => $offer['name'] . ' ' . ($offer['params']['term'] / 12) . ' years'
                                . ($offer['params']['upfront_payment_gross'] > 0 ? ' (' . Number::currency($offer['params']['upfront_payment_gross'], 'GBP', precision: 0)  . ' up front)' : ''),
                            'type' => 'lease',
                            'amount' => $amount,
                            'payment_provider_id' => $paymentProviderId,
                            'term' => $offer['params']['term'],
                            'priority' => $offer['rank'],
                            'upfront_payment' => $offer['params']['upfront_payment_gross'] ?? 0,
                            'first_payment' => $offer['params']['min_payments_gross'][0] ?? 0,
                            'monthly_payment' => $offer['params']['monthly_payment_gross'],
                            'final_payment' => $offer['params']['monthly_payment_gross'],
                            'minimum_payments' => $offer['params']['min_payments_gross'],
                            'total_repayable' => collect($offer['params']['min_payments_gross'])->sum(),
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

            Log::debug('Hometree poll complete.', [$surveyId, $applicationId, $every, $for]);
        })->delay(now()->addSeconds($delay ?? 0));
    }

    protected function upsertPaymentOffer(array $data): PaymentOffer
    {
        // TODO: Should be able to use updateOrCreate now the odder IDs are fixed in the API
        $paymentOffer = PaymentOffer::firstWhere('provider_offer_id', $data['provider_offer_id']);

        if ($paymentOffer) {
//            Log::debug('Updating Hometree offer...', collect($data)->only([
//                'payment_survey_id',
//                'name',
//                'type',
//                'amount',
//                'payment_provider_id',
//                'status',
//                'provider_application_id',
//                'provider_offer_id',
//            ])->toArray());

            $paymentOffer->update($data);
            return $paymentOffer;
        }

//        Log::debug('Inserting Hometree offer...', collect($data)->only([
//            'payment_survey_id',
//            'name',
//            'type',
//            'amount',
//            'payment_provider_id',
//            'status',
//            'provider_application_id',
//            'provider_offer_id',
//        ])->toArray());

        return PaymentOffer::create($data);
    }

}
