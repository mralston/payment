<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Mralston\Payment\Data\CancellationData;
use Mralston\Payment\Events\PaymentCancelled;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Interfaces\PaymentParentModel;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Models\PaymentProvider;
use Mralston\Payment\Models\PaymentStatus;
use Mralston\Payment\Models\PaymentSurvey;
use Mralston\Payment\Services\PaymentService;
use Mralston\Payment\Traits\BootstrapsPayment;

class PaymentController
{
    use BootstrapsPayment;

    public function __construct(
        private PaymentHelper $helper,
        private PaymentService $paymentService,
    ) {
        //
    }

    public function index()
    {
        return Inertia::render('Payment/Index', [
            'payments' => Payment::query()
                ->where('payment_status_id', '!=', PaymentStatus::byIdentifier('new')?->id)
                ->with([
                    'parentable',
                    'paymentProvider',
                    'parentable.user',
                    'paymentStatus',
                ])
                ->orderBy('updated_at', 'desc')
                ->paginate(10),
            'parentRouteName' => $this->helper->getParentRouteName(),
            'parentModelDescription' => config('payment.parent_model_description'),
        ])
            ->withViewData($this->helper->getViewData());
    }

    public function start(int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        // If the payment process is disabled, throw them out
        if ($result = $this->helper->disablePaymentProcess()) {

            if (is_string($result)) {
                $reason = $result;
            } else {
                $reason = 'Payment process is disabled';
            }

            return Inertia::render('Payment/Disabled', [
                'reason' => $reason,
            ])->withViewData($this->helper->getViewData());
        }

        // If the survey has been filled in, go to the options page
        if ($parentModel->paymentSurvey?->basic_questions_completed) {
            return redirect()
                ->route('payment.options', ['parent' => $parent]);
        }

        // Go to the survey
        return redirect()
            ->route('payment.surveys.create', ['parent' => request()->route('parent')]);
    }

    public function cancel(Request $request, int $parent, Payment $payment)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        $this->paymentService->cancel(
            new CancellationData(
                paymentId: $payment->id,
                paymentStatusIdentifier: $request->input('payment_status_identifier'),
                reason: $request->input('cancellation_reason'),
                source: $request->input('source'),
                userId: Auth::id(),
                disableChangePaymentMethodAfterCancellation: $request->boolean('disableChangePaymentMethodAfterCancellation'),
            )
        );

        if ($request->input('redirect')) {
            return redirect($request->input('redirect'));
        }

        return redirect(route('payments.show', $payment));
    }

    public function show(Payment $payment)
    {
        $survey = $payment->parentable->paymentSurvey;

        $helper = app(PaymentHelper::class)
            ->setParentModel($payment->parentable);

        return Inertia::render('Payment/Show', [
            'payment' => $payment
                ->load([
                    'paymentProvider',
                    'paymentStatus',
                    'parentable',
                    'parentable.user',
                    'paymentCancellations',
                    'paymentCancellations.user',
                    'paymentOffer',
                    'employmentStatus',
                ]),
            'products' => $helper->getBasketItems(),
        ])->withViewData($this->helper->getViewData());
    }

    public function locked(Request $request, int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        $reason = 'You cannot start another payment.';

        if (is_string($this->helper->disableChangePaymentMethodAfterCancellation())) {
            $reason .= ' ' . $this->helper->disableChangePaymentMethodAfterCancellation();
        }

        return Inertia::render('Payment/Disabled', [
            'reason' => $reason,
        ])
            ->withViewData($this->helper->getViewData());
    }
}
