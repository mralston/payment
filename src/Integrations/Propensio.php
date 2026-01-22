<?php

namespace Mralston\Payment\Integrations;

use App\Address;
use App\FinanceApplication;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Mralston\Payment\Data\PrequalData;
use Mralston\Payment\Data\PrequalPromiseData;
use Mralston\Payment\Data\PropensioRequestData;
use Mralston\Payment\Events\OffersReceived;
use Mralston\Payment\Interfaces\FinanceGateway;
use Mralston\Payment\Interfaces\PaymentGateway;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Interfaces\PrequalifiesCustomer;
use Mralston\Payment\Interfaces\Apiable;
//use Mralston\Payment\Interfaces\Signable;
use Mralston\Payment\Interfaces\WantsEpvs;
use Mralston\Payment\Mail\CancelManually;
use Mralston\Payment\Mail\SatNoteUpload;
use Mralston\Payment\Enums\LookupField;
use Mralston\Payment\Enums\PaymentStage as PaymentStageEnum;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentLookupField;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Models\PaymentProvider;
use Mralston\Payment\Models\PaymentStatus;
use Mralston\Payment\Models\PaymentSurvey;
use Mralston\Payment\Models\PaymentStage;
use Mralston\Payment\Services\PaymentCalculator;
use Mralston\Payment\Services\PropensioService;
use Mralston\Payment\Traits\HandlesPaymentErrors;
use Mralston\Payment\Traits\HandlesPaymentResponse;
use Mralston\Payment\Traits\RecordsPaymentError;
use Spatie\ArrayToXml\ArrayToXml;

class Propensio implements PaymentGateway, FinanceGateway, PrequalifiesCustomer, WantsEpvs, Apiable /*, Signable*/
{
    use HandlesPaymentErrors;
    use HandlesPaymentResponse;
    use RecordsPaymentError;

    private array $environmentCodes = [
        'local' => 'UAT',
        'dev' => 'UAT',
        'testing' => 'UAT',
        'production' => 'UAT',
    ];

    /**
     * Environment code to be used when talking to the Propensio API.
     *
     * @var string
     */
    private string $environmentCode;

    private PropensioService $propensioService;

    private $requestData = null;

    private $responseData = null;

    public function __construct(
        private string $username,
        private string $password,
        string $endpoint,
    ) {
        $this->environmentCode = $this->environmentCodes[$endpoint];

        $this->propensioService = new PropensioService(
            $endpoint,
            $username,
            $password,
        );
    }

    /**
     * Checks whether the API is functional.
     *
     * @return bool
     */
    public function healthCheck(): bool
    {
        return false;
    }

    public function prequal(PaymentSurvey $survey, float $totalCost): PrequalPromiseData|PrequalData
    {
        dispatch(function () use ($survey, $totalCost) {
            $helper = app(PaymentHelper::class)
                ->setParentModel($survey->parentable);

            $amount = $helper->getTotalCost() - $survey->finance_deposit;

            $paymentProvider = PaymentProvider::byIdentifier('propensio');

            $products = $paymentProvider->paymentProducts;

            $deposit = $survey->finance_deposit;
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
                // Fetch products available from lender

                $reference = $helper->getReference() . '-' . Str::of(Str::random(5))->upper();

                $calculator = app(PaymentCalculator::class);

                // Store products to offers
                $offers = collect();
                
                $products->map(function ($product) use (
                    $offers,
                    $survey,
                    $paymentProvider,
                    $reference,
                    $calculator,
                    $totalCost,
                    $amount,
                    $deposit,
                ) {

                    $payments = $calculator->calculate($amount, $product->apr, $product->term, $product->deferred ?? 0);

                    $offers->push($survey->parentable
                        ->paymentOffers()
                        ->create([
                            'payment_survey_id' => $survey->id,
                            'payment_provider_id' => $paymentProvider->id,
                            'payment_product_id' => $product->id,
                            'name' => $product->name,
                            'type' => 'finance',
                            'reference' => $reference,
                            'total_cost' => $totalCost,
                            'amount' => $amount,
                            'deposit' => $deposit,
                            'apr' => $product->apr,
                            'term' => $product->term,
                            'deferred' => ($product->deferred ?? 0) > 0 ? ($product->deferred ?? 0) : null,
                            'deferred_type' => $product->deferred_type ?? null,
                            'first_payment' => $payments['firstPayment'],
                            'monthly_payment' => $payments['monthlyPayment'],
                            'final_payment' => $payments['finalPayment'],
                            'total_payable' => $payments['total'],
                            'status' => 'final',
                        ]));
                });
            }

            event(new OffersReceived(
                gateway: static::class,
                type: 'finance',
                surveyId: $survey->id,
                offers: $offers,
            ));
        });

        return new PrequalPromiseData(
            gateway: static::class,
            type: 'finance',
            surveyId: $survey->id,
        );
    }

    public function apply(Payment $payment): Payment
    {
        $helper = app(PaymentHelper::class)
            ->setParentModel($payment->parentable);

        $propensioRequestData = new PropensioRequestData(
            $payment,
            $this->environmentCode,
            $this->getNewRef(),
            $helper->getGross()
        );

        $propensioRequestData->build();

        $this->propensioService->sendApplicationRequest(
            $propensioRequestData->get()
        );

        if ($this->hasErrors($this->propensioService->getLastResponse(), 'propensio')) {

            //{"errors": [{"errorMsg": "Term is less than the minimum value allowed for this product (i.e. 24)", "errorCode": "term", "errorValue": 12}, {"errorMsg": "Asset type must be 'NO ASSET' or 'STATIC_CARAVAN'", "errorCode": "assetType"}, {"errorMsg": "Mobile phone is required", "errorCode": "mobilePhone"}, {"errorMsg": "Current address city is required", "errorCode": "caCity"}, {"errorMsg": "Error code altCity_required", "errorCode": "altCity"}]}
            $paymentStatus = PaymentStatus::byIdentifier('error');
            $payment->update([
                'payment_status_id' => $paymentStatus?->id,
                'provider_request_data' => $propensioRequestData->get(),
                'provider_response_data' => $this->normalizeErrors(
                    $this->propensioService->getLastResponse(),
                    'propensio',
                )
            ]);

            $this->recordError(
                $payment,
                PaymentStage::byIdentifier(PaymentStageEnum::APPLY->value),
                $this->propensioService->getLastResponse(),
            );
            
            return $payment;
        }

        $responseNormalized = $this->normalizeResponse(
            $this->propensioService->getLastResponse(),
            'propensio'
        );

        $payment->update([
            //'payment_status_id' => $paymentStatus?->id,
            'provider_request_data' => $propensioRequestData->get(),
            'provider_response_data' => $this->propensioService->getLastResponse(),
            'provider_foreign_id' => $responseNormalized->applicationId,
            'provider_application_number' => $responseNormalized->applicationNumber,
        ]);

        return $payment;
    }

    public function cancel(Payment $payment, ?string $reason = null): bool
    {
        if (
            $this->propensioService->cancelApplicationRequest(
                $payment->provider_application_number
            )
        ) {
            return true;
        } else {

            $this->recordError(
                $payment,
                PaymentStage::byIdentifier(PaymentStageEnum::CANCEL->value),
                $this->propensioService->getLastResponse(),
            );

            return false;
        }
    }

    public function getCancellationResponse(): array
    {
        return $this->propensioService->getLastResponse();
    }

    public function sendSatNote(Payment $payment)
    {
        Log::channel('payment')->info('Sending sat note by e-mail');

        $this->propensioService->uploadSatisactionNote(
            $payment->provider_application_number,
            'Sat Note ' . $this->payment->provider_foreign_id . '.pdf',
            $this->payment->sat_note->dir
        );
        
        if ($this->hasErrors($this->propensioService->getLastResponse(), 'propensio')) {

            $this->recordError(
                $payment,
                PaymentStage::byIdentifier(PaymentStageEnum::SAT_NOTE_UPLOAD->value),
                $this->propensioService->getLastResponse(),
            );

            throw new \Exception('Sat note upload failed');
        }

        Log::channel('payment')->info(
            $this->propensioService->getLastResponse()
        );

    }

    public function pollStatus(Payment $payment): array
    {
        $this->propensioService
            ->getApplicationRequest($payment->provider_foreign_id);

        $response = $this
            ->normalizeResponse(
                $this->propensioService->getLastResponse(),
                'propensio'
            );

        if ($this->hasErrors($this->propensioService->getLastResponse(), 'propensio')) {
            $this->recordError(
                $payment,
                PaymentStage::byIdentifier(PaymentStageEnum::STATUS_POLL->value),
                $this->propensioService->getLastResponse(),
            );

            throw new \Exception('Status not found');
        }

        if (is_null($response->statusId)) {

            $this->recordError(
                $payment,
                PaymentStage::byIdentifier(PaymentStageEnum::STATUS_POLL->value),
                ['error' => 'Status not found'],
            );

            throw new \Exception('Status not found');
        }

        $payment->update([
            'payment_status_id' => $response->statusId,
        ]);

        return [
            'status' => $response->statusName,
            'lender_response_data' => $this->propensioService->getLastResponse(),
            'offer_expiration_date' => null
        ];
    }

    public function calculatePayments(int $loanAmount, float $apr, int $loanTerm, ?int $deferredPeriod = null): array
    {
        return [];
    }

    public function financeProducts(): Collection
    {
        return collect([]);
    }

    public function cancelOffer(PaymentOffer $paymentOffer): void
    {
        // Stub to satisfy interface, no action required.
    }

    public function uploadEpvsCertificate(Payment $payment, string $encodedFile): bool
    {
        return false;
    }

    public function getRequestData(): ?array
    {
        return $this->requestData;
    }

    public function getResponseData(): ?array
    {
        return $this->responseData;
    }

    public function getNewRef(int $length = 32)
    {
        return substr(str_replace('-', '', Str::uuid()), 0, $length);
    }
}
