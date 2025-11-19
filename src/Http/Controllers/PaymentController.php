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
use Mralston\Payment\Interfaces\Signable;
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
                    'paymentProduct',
                ])
                ->join('payment_providers', 'payments.payment_provider_id', '=', 'payment_providers.id')
                ->join('payment_statuses', 'payments.payment_status_id', '=', 'payment_statuses.id')
                ->join($parentTable, 'payments.parentable_id', '=', $parentTable . '.id')
                ->join($userTable, $parentTable . '.user_id', '=', $userTable . '.id')
                ->where(function ($query) use ($request, $userTable) {
                    $query
                        ->when(!empty($request->input('search.id')), fn($query) => $query->where('payments.id', $request->input('search.id')))
                        ->when(!empty($request->input('search.created_at')), fn($query) => $query->where('payments.created_at', 'like', $request->input('search.created_at') . '%'))
                        ->when(!empty($request->input('search.reference')), fn($query) => $query->where('payments.reference', 'like', '%' . $request->input('search.reference') . '%'))
                        ->when(!empty($request->input('search.parentable_id')), fn($query) => $query->where('payments.parentable_id', $request->input('search.parentable_id')))
                        ->when(!empty($request->input('search.customer')), fn($query) => $query->whereRaw('CONCAT(payments.first_name, \' \', payments.last_name) LIKE ?', ['%' . $request->input('search.customer') . '%']))
                        ->when(!empty($request->input('search.post_code')), fn($query) => $query->whereRaw('REPLACE(LOWER(addresses->>\'$[0].postCode\'), \' \', \'\') LIKE LOWER(?)', ['%' . Str::replace(' ', '', $request->input('search.post_code')) . '%']))
                        ->when(!empty($request->input('search.amount')), fn($query) => $query->where('payments.amount', $request->input('search.amount')))
                        ->when(!empty($request->input('search.deposit')), fn($query) => $query->where('payments.deposit', $request->input('search.deposit')))
                        ->when(!empty($request->input('search.apr')), fn($query) => $query->where('payments.apr', $request->input('search.apr')))
                        ->when(!empty($request->input('search.term')), fn($query) => $query->where('payments.term', $request->input('search.term')))
                        ->when(!empty($request->input('search.deferred')), fn($query) => $query->where('payments.deferred', $request->input('search.deferred')))
                        ->when(!empty($request->input('search.status')), fn($query) => $query->where('payment_statuses.name', 'like', '%' . $request->input('search.status') . '%'))
                        ->when(!empty($request->input('search.gateway')), fn($query) => $query->where('payment_providers.name', 'like', '%' . $request->input('search.gateway') . '%'))
                        ->when(!empty($request->input('search.subsidy')), fn($query) => $query->where('payments.subsidy', $request->input('search.subsidy')))
                        ->when(!empty($request->input('search.user')), fn($query) => $query->where($userTable . '.name', 'like', '%' . $request->input('search.user') . '%'));
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
                ->paginate($request->input('per_page', 20))
                ->appends([
                    'search' => $request->input('search'),
                    'sort' => $request->input('sort', 'created_at'),
                    'direction' => $request->input('direction', 'desc'),
                ]),
            'parentRouteName' => $this->helper->getParentRouteName(),
            'parentModelDescription' => config('payment.parent_model_description'),
            'search' => $request->input('search') == '' ? null : $request->input('search'),
            'sort' => $request->input('sort', 'created_at'),
            'direction' => $request->input('direction', 'desc'),
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
        $helper = app(PaymentHelper::class);

        if (!empty($payment->parentable)) {
            $helper->setParentModel($payment->parentable);
        }

        return Inertia::render('Payment/Show', [
            'payment' => $payment
                ->load([
                    'paymentProvider',
                    'paymentStatus',
                    'paymentProduct',
                    'parentable',
                    'parentable.user',
                    'paymentCancellations',
                    'paymentCancellations.user',
                    'paymentOffer',
                    'employmentStatus',
                    'nationalityValue',
                    'satNoteFile',
                ]),
            'products' => !empty($payment->parentable) ? $helper->getBasketItems() : [],
            'paymentProviderSupportsRemoteSigning' => $payment
                ->paymentProvider
                ->gateway() instanceof Signable,
            'parentModelDescription' => config('payment.parent_model_description'),
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

    public function moveCheck(Request $request, Payment $payment, int $parentableId)
    {
        $targetParentable = app(config('payment.parent_model'))->findOrFail($parentableId);

        if ($targetParentable->id == $payment->parentable->id) {
            return response()->json([
                'error' => config('payment.parent_model_description') . ' is already associated with the payment.'
            ], 403);
        }

        if (!$this->paymentService->isPaymentCompatible($payment, $targetParentable)) {

            $compatibility = $this->paymentService->paymentCompatibility($payment, $targetParentable);

            return response()->json([
                'error' => 'Payment is not compatible with the target ' . Str::lower(config('payment.parent_model_description')) . '.',
                'compatibility' => $compatibility
            ], 200);
        }

        return response()->json($targetParentable->payments()->with('paymentStatus')->get(), 200);
    }

    public function move(Request $request, Payment $payment, int $parentableId)
    {
        $targetParentable = app(config('payment.parent_model'))->findOrFail($parentableId);

        if ($payment->parentable->id == $targetParentable->id) {
            return response()->json([
                'error' => 'Payment is already associated with the target ' . Str::lower(config('payment.parent_model_description')) . '.'
            ], 403);
        }

        $this->paymentService->move($payment, $targetParentable);

        return redirect()
            ->back()
            ->with('success', 'Payment moved successfully.');
    }
}
