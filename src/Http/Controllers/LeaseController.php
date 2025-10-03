<?php

namespace Mralston\Payment\Http\Controllers;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Mralston\Payment\Enums\LookupField;
use Mralston\Payment\Enums\PaymentType as PaymentTypeEnum;
use Mralston\Payment\Http\Requests\SubmitLeaseApplicationRequest;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Jobs\WaitToSubmitPayment;
use Mralston\Payment\Jobs\WatchForPaymentUpdates;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentLookupField;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Models\PaymentProduct;
use Mralston\Payment\Models\PaymentProvider;
use Mralston\Payment\Models\PaymentStatus;
use Mralston\Payment\Models\PaymentType;
use Mralston\Payment\Services\LeaseService;
use Mralston\Payment\Traits\BootstrapsPayment;

class LeaseController
{
    use BootstrapsPayment;

    public function __construct(
        protected PaymentHelper $helper,
        protected LeaseService $leaseService,
    ) {
        //
    }

    public function create(Request $request, int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        $offer = PaymentOffer::findOrFail($request->get('offerId'));
        $survey = $parentModel->paymentSurvey;

        if (!$survey->basic_questions_completed) {
            return redirect()
                ->route('payment.surveys.lease', ['parent' => $parentModel, 'survey' => $survey, 'offerId' => $request->get('offerId')]);
        }

        return Inertia::render('Lease/Create', [
            'parentModel' => $parentModel,
            'survey' => $survey,
            'offer' => $offer->load('paymentProvider'),
            'totalCost' => $this->helper->getTotalCost(),
            'deposit' => $survey->lease_deposit,
            'companyDetails' => $this->helper->getCompanyDetails(),
            'paymentProviders' => PaymentProvider::query()
                ->whereHas('paymentType', function ($query) {
                    $query->whereIdentifier(PaymentTypeEnum::LEASE->value);
                })
                ->get(),
            'employmentStatuses' => PaymentLookupField::byIdentifier(LookupField::EMPLOYMENT_STATUS)
                ->paymentLookupValues,
            'canChangePaymentMethod' => $this->helper->canChangePaymentMethod(),
        ])
            ->withViewData($this->helper->getViewData());
    }

    public function store(SubmitLeaseApplicationRequest $request, int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        $survey = $parentModel->paymentSurvey;
        $offer = PaymentOffer::findOrFail($request->get('offerId'));

        // Create payment
        $payment = Payment::make()
            ->withPaymentType(PaymentType::byIdentifier(PaymentTypeEnum::LEASE))
            ->withPaymentProduct(PaymentProduct::find($offer->payment_product_id))
            ->withSurvey($survey)
            ->withOffer($offer)
            ->setParent($parentModel)
            ->fill([
                'deposit' => $survey->lease_deposit,
                'eligible' => $request->get('eligible'),
                'gdpr_opt_in' => $request->get('gdprOptIn'),
                'read_terms_conditions' => $request->get('readTermsConditions'),
            ]);
        $payment->save();

        $gateway = $payment->paymentProvider->gateway();

        try {
            // Fetch the application from the provider
            $application = $gateway->getApplication($offer->provider_application_id);

            // See whether we need to send applicants (this happens when the survey is skipped)
            if ($application['status'] == 'pending-applicants') {
                // Send the applicants
                $response = $gateway->updateApplication($survey, $offer->provider_application_id);

                $result = $payment->update([
                    'provider_request_data' => $gateway->getRequestData() ?? $payment->provider_request_data,
                    'provider_response_data' => $gateway->getResponseData() ?? $payment->provider_response_data,
                    'payment_status_id' => PaymentStatus::byIdentifier($response['status'])?->id,
                ]);

                Log::debug('update result: ' . $result ? 'success' : 'failure');
            }
        } catch (RequestException $e) {
            $payment->update([
                'provider_request_data' => $gateway->getRequestData() ?? $payment->provider_request_data,
                'provider_response_data' => $gateway->getResponseData() ?? $payment->provider_response_data ?? (string)$e->response?->getBody() ?? $e->getMessage(),
                'submitted_at' => now(),
                'payment_status_id' => PaymentStatus::byIdentifier('error')?->id,
            ]);

            return redirect()
                ->route('payment.lease.show', [
                    'parent' => $parent,
                    'lease' => $payment,
                ]);
        } catch (Exception $e) {
            $payment->update([
                'provider_request_data' => $gateway->getRequestData() ?? $payment->provider_request_data,
                'provider_response_data' => $gateway->getResponseData() ?? $payment->provider_response_data ?? $e->getMessage(),
                'submitted_at' => now(),
                'payment_status_id' => PaymentStatus::byIdentifier('error')?->id,
            ]);

            return redirect()
                ->route('payment.lease.show', [
                    'parent' => $parent,
                    'lease' => $payment,
                ]);
        }

        // Check the status
        $response = $gateway->getApplication($offer->provider_application_id);
        $payment->update([
            'payment_status_id' => PaymentStatus::byIdentifier($response['status'])?->id,
        ]);

        // Decide whether to submit immediately or wait in the background until ready
        if ($response['status'] == 'processing') {
            Log::debug('backgrounding');
            // Need to wait until it's ready. We'll do that in the background
            WaitToSubmitPayment::dispatch($payment, $offer);
        } else {
            Log::debug('foregrounding');
            // Application is ready. Submit it now
            $result = $this->leaseService->submitApplication($gateway, $payment, $offer, $survey, $parent);
        }

        // Watch the status in the background for a little while and see if it updates
        WatchForPaymentUpdates::dispatch($payment->id);

        return redirect()
            ->route('payment.lease.show', [
                'parent' => $parent,
                'lease' => $payment,
            ]);
    }

    public function show(int $parent, Payment $lease)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);
        $payment = $lease;

        return Inertia::render('Lease/Show', [
            'parentModel' => $parentModel,
            'payment' => $payment->load([
                'paymentProvider',
                'paymentStatus',
                'paymentProduct',
            ]),
            'survey' => $payment->paymentOffer?->paymentSurvey,
            'offer' => $payment->paymentOffer,
            'disableChangePaymentMethodAfterCancellation' => boolval($this->helper->disableChangePaymentMethodAfterCancellation()),
            'disableChangePaymentMethodAfterCancellationReason' => strval($this->helper->disableChangePaymentMethodAfterCancellation()),
        ])
            ->withViewData($this->helper->getViewData());
    }
}
