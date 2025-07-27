<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Support\Str;
use Inertia\Inertia;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Interfaces\PaymentParentModel;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentStatus;
use Mralston\Payment\Services\PrequalService;
use Mralston\Payment\Traits\BootstrapsPayment;

class PaymentController
{
    use BootstrapsPayment;

    public function __construct(
        private PaymentHelper $helper,
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

    public function options(int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        $this->setDefaultDeposit($parentModel);

        return Inertia::render('Payment/Options', [
            'parentModel' => $parentModel,
            'survey' => $parentModel->paymentSurvey,
            'customers' => $this->helper->getCustomers(),
            'totalCost' => $this->helper->getTotalCost(),
            'deposit' => $this->helper->getDeposit(),
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
}
