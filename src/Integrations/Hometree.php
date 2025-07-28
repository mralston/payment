<?php

namespace Mralston\Payment\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mralston\Payment\Data\PrequalPromiseData;
use Mralston\Payment\Data\Offers;
use Mralston\Payment\Events\OffersReceived;
use Mralston\Payment\Events\PrequalError;
use Mralston\Payment\Interfaces\LeaseGateway;
use Mralston\Payment\Interfaces\PaymentGateway;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Interfaces\PrequalifiesCustomer;
use Mralston\Payment\Models\PaymentSurvey;

class Hometree implements PaymentGateway, LeaseGateway, PrequalifiesCustomer
{
    /**
     * Endpoints to be used based on environment.
     *
     * @var array|string[]
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
    ) {
        $helper = app(PaymentHelper::class)
            ->setParentModel($survey->parentable);

        $firstCustomer = $survey->customers->first();
        $firstAddress = $survey->addresses->first();

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
                    'generation_month_12_kwh' => $helper->getGeneration(),
                    'savings_month_12_solar_gross' => $helper->getSolarSavings(),
                    'savings_month_12_ess_gross' => $helper->getBatterySavings(),
                ],
                'price' => [
                    'net_value' => $helper->getNet(),
                    'vat' => $helper->getVatRate(),
                    'vat_value' => $helper->getVat(),
                ]
            ],
//            'applicants' => $survey->customers
//                ->map(function ($customer) use ($survey, $firstAddress) {
//                    return [
//                        'first_name' => $customer['firstName'],
//                        'middle_name' => $customer['middleName'] ?? '',
//                        'last_name' => $customer['lastName'],
//                        'email' => $customer['email'],
//                        'mobile_phone_number' => $customer['phone'],
//                        'dob' => $customer['dateOfBirth'],
//                        'address' => [
//                            'udprn' => $firstAddress['udprn'],
//                        ],
//                        'previous_address' => $survey->addresses
//                            ->map(function ($address) {
//                                return [
//                                    'udprn' => $address['udprn'],
//                                ];
//                            })->toArray(),
//                        'affordability' => [
//                            'gross_annual_income' => $customer['grossAnnualIncome'],
//                            'dependants' => $customer['dependants'],
//                            'employment_status' => $customer['employmentStatus'],
//                        ]
//                    ];
//                }),
            'reference' => $helper->getReference() . '-' . Str::of(Str::random(5))->upper(),
        ];

        dump(json_encode($payload));


//        dd(json_encode($payload, JSON_PRETTY_PRINT));

//        try {
            $response = Http::baseUrl($this->endpoint)
                ->withHeader('X-Client-App', config('payment.hometree.client_id', 'Hometree'))
                ->withToken($this->key, 'Token')
                ->post('/applications', $payload)
                ->throw()
                ->json();
//        } catch (\Throwable $ex) {
//            Log::error('Failed to creat application on Hometree API.');
//            Log::error('Error #' . $ex->getCode() . ': ' . $ex->getMessage());
//            Log::error($ex->response->body());
//            Log::error('URL: ' . $this->endpoint . '/applications');
//            throw $ex;
//        }

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
            //sleep(5); // Fake a delay during development

            try {
                $response = $this->createApplication($survey);


                $offers = collect(); // Mock for actual functionality


                event(new OffersReceived(
                    gateway: static::class,
                    surveyId: $survey->id,
                    offers: $offers,
                ));
            } catch (\Throwable $ex) {
                // Broadcast an error event
                event(new PrequalError(
                    gateway: static::class,
                    type: 'lease',
                    surveyId: $survey->id,
                    errorCode: $ex->getCode(),
                    errorMessage: $ex->getMessage(),
                    response: (string)$ex->response?->getBody(),
                ));
            }
        });

        return new PrequalPromiseData(
            gateway: static::class,
            surveyId: $survey->id,
        );
    }

}
