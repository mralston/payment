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
use Mralston\Payment\Traits\HandlesPrequal;

class Propensio implements PaymentGateway, FinanceGateway, PrequalifiesCustomer, WantsEpvs, Apiable /*, Signable*/
{
    use HandlesPaymentErrors;
    use HandlesPaymentResponse;
    use RecordsPaymentError;
    use HandlesPrequal;

    private array $environmentCodes = [
        'local' => 'UAT',
        'dev' => 'UAT',
        'testing' => 'UAT',
        'staging' => 'UAT',
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
        return $this->runPrequal(
            provider: 'propensio',
            survey: $survey,
            totalCost: $totalCost
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
                'provider_response_data' => $this->normaliseErrors(
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

        $responseNormalized = $this->normaliseResponse(
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
            ->normaliseResponse(
                $this->propensioService->getLastResponse(),
                'propensio'
            );

        if ($this->hasErrors($this->propensioService->getLastResponse(), 'propensio')) {
            $this->recordError(
                $payment,
                PaymentStage::byIdentifier(PaymentStageEnum::STATUS_POLL->value),
                $this->normaliseErrors(
                    $this->propensioService->getLastResponse(),
                    'propensio'
                ),
            );

            throw new \Exception('Status not found');
        }

        if (is_null($response->statusId)) {

            $this->recordError(
                $payment,
                PaymentStage::byIdentifier(PaymentStageEnum::STATUS_POLL->value),
                [
                    'applicationStatusCode' => $this
                        ->propensioService
                        ->getLastResponse()['results']['loan']['applicationStatusCode']
                ],
            );

            throw new \Exception('Status not found');
        }

        $payment->update([
            'payment_status_id' => $response->statusId,
            'monthly_payment' => $response->monthlyPayment,
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
