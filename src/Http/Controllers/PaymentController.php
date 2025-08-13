<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Mralston\Payment\Data\CancellationData;
use Mralston\Payment\Events\PaymentCancelled;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Interfaces\PaymentParentModel;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentProvider;
use Mralston\Payment\Models\PaymentStatus;
use Mralston\Payment\Services\PaymentService;
use Mralston\Payment\Traits\BootstrapsPayment;
use Mralston\Payment\Traits\RedirectsOnActivePayment;

class PaymentController
{
    use BootstrapsPayment;
    use RedirectsOnActivePayment;

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

    public function options(int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        $this->redirectToActivePayment($parentModel);

        $this->setDefaultDeposit($parentModel);

        $survey = $parentModel->paymentSurvey;

        if (empty($survey)) {
            $survey = $parentModel->paymentSurvey()->create([
                'customers' => $this->helper->getCustomers(),
                'addresses' => [$this->helper->getAddress()],
            ]);
        }

        return Inertia::render('Payment/Options', [
            'parentModel' => $parentModel,
            'survey' => $survey->load([
                'paymentOffers' => fn ($query) => $query->where('selected', false),
                'paymentOffers.paymentProvider',
            ]),
            'customers' => $this->helper->getCustomers(),
            'totalCost' => $this->helper->getTotalCost(),
            'deposit' => $this->helper->getDeposit(),
            'leaseMoreInfoContent' => $this->helper->getLeaseContent(),
            'paymentProviders' => PaymentProvider::all(),
            'systemSavings' => $this->helper->getSystemSavings(),
        ])->withViewData($this->helper->getViewData());
    }

    private function setDefaultDeposit(PaymentParentModel $parentModel)
    {
        if (empty($this->helper->getDeposit()) && !empty(config('payment.deposit'))) {

            if (Str::of(config('payment.deposit'))->endsWith('%')) {
                // Calculate deposit as percentage of total
                $percentage = Str::of(config('payment.deposit'))->beforeLast('%')->__toString();
                $deposit = floatval($percentage) / 100 * $this->helper->getTotalCost();


            } else {
                // Standard numeric deposit values
                $deposit = config('payment.deposit');
            }

            // Set the deposit
            return $this->helper->setDeposit($deposit);
        }
    }

    public function cancel(Request $request, int $parent, Payment $payment)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        $this->paymentService->cancel(
            new CancellationData(
                paymentId: $payment->id,
                paymentStatusIdentifier: $request->payment_status_identifier,
                reason: $request->cancellation_reason,
                source: $request->source,
                userId: auth()->user()->id,
            )
        );

        event(new PaymentCancelled($payment));

        return Inertia::location(route('payments.show', $payment));
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
                    'paymentOffer',
                    'employmentStatus',
                ]),
            'products' => $helper->getBasketItems(),
        ])->withViewData($this->helper->getViewData());
    }
}
