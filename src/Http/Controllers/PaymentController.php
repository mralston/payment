<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function index(Request $request)
    {
        $parentTable = app(config('payment.parent_model'))->getTable();
        $userTable = app(config('payment.user_model'))->getTable();

        return Inertia::render('Payment/Index', [
            'payments' => Payment::select('payments.*')
                ->whereHas('paymentStatus', function ($query) {
                    $query->where('unlisted', false);
                })
                ->with([
                    'parentable',
                    'paymentProvider',
                    'parentable.user',
                    'paymentStatus',
                ])
                ->join('payment_providers', 'payments.payment_provider_id', '=', 'payment_providers.id')
                ->join('payment_statuses', 'payments.payment_status_id', '=', 'payment_statuses.id')
                ->join($parentTable, 'payments.parentable_id', '=', $parentTable . '.id')
                ->join($userTable, $parentTable . '.user_id', '=', $userTable . '.id')
                ->where(function ($query) use ($request, $userTable) {
                    $query->where('payments.id', $request->input('search'))
                        ->orWhere('payments.reference', 'LIKE', '%' . $request->input('search') . '%')
                        ->orWhere('payments.parentable_id', $request->input('search'))
                        ->orWhere('payments.first_name', 'LIKE', '%' . $request->input('search') . '%')
                        ->orWhere('payments.last_name', 'LIKE', '%' . $request->input('search') . '%')
                        ->orWhereRaw('addresses->>\'$[0].postCode\' LIKE ?', '%' . $request->input('search') . '%')
                        ->orWhere('payment_statuses.name', 'LIKE', '%' . $request->input('search') . '%')
                        ->orWhere('payment_providers.name', 'LIKE', '%' . $request->input('search') . '%')
                        ->orWhere($userTable . '.name', 'LIKE', '%' . $request->input('search') . '%')
                    ;
                })
                ->when($request->filled('sort'), function ($query) use ($request, $userTable) {
                    $dir = strtolower($request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
                    $field = $request->input('sort');
                    $map = [
                        'id' => 'payments.id',
                        'created_at' => 'payments.created_at',
                        'reference' => 'payments.reference',
                        'parent' => 'payments.parentable_id',
                        'customer' => DB::raw('CONCAT(payments.first_name, \' \', payments.last_name)'),
                        'post_code' => DB::raw('addresses->>\'$[0].postCode\''),
                        'amount' => 'payments.amount',
                        'deposit' => 'payments.deposit',
                        'apr' => 'payments.apr',
                        'term' => 'payments.term',
                        'deferred' => 'payments.deferred',
                        'status' => 'payments.payment_status_id',
                        'gateway' => 'payment_providers.name',
                        'subsidy' => 'payments.deposit',
                        'user' => $userTable . '.name',
                    ];
                    if (array_key_exists($field, $map)) {
                        $query->orderBy($map[$field], $dir);
                    }
                }, function ($query) {
                    $query->orderBy('payments.created_at', 'desc');
                })
                ->paginate($request->input('per_page', 10))
                ->appends([
                    'search' => $request->input('search'),
                    'sort' => $request->input('sort'),
                    'direction' => $request->input('direction'),
                ]),
            'parentRouteName' => $this->helper->getParentRouteName(),
            'parentModelDescription' => config('payment.parent_model_description'),
            'search' => $request->input('search'),
            'sort' => $request->input('sort'),
            'direction' => $request->input('direction', 'asc'),
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
