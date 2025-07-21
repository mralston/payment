<?php

namespace Mralston\Payment\Http\Controllers;

use Inertia\Inertia;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Interfaces\PaymentParentModel;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentStatus;
use Mralston\Payment\Traits\BootstrapsPayment;

class PaymentController
{
    use BootstrapsPayment;

    public function __construct(
        private PaymentHelper $helper
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
                    'paymentStatus'
                ])
                ->orderBy('updated_at', 'desc')
                ->paginate(10),
            'parentRouteName' => $this->helper->getParentRouteName(),
        ])
            ->withViewData($this->helper->getViewData());
    }

    public function choosePaymentOption(int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        return Inertia::render('Payment/ChoosePaymentOption', [
            'parentModel' => $parentModel,
            'customers' => $this->helper->getCustomers(),
        ])->withViewData($this->helper->getViewData());
    }

    public function prequal(int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);


    }
}
