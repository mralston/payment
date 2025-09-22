<?php

namespace Mralston\Payment\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use Mralston\Payment\Events\PriceChanged;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Traits\BootstrapsPayment;

class UpdateCashDeposit implements ShouldQueue
{
    use BootstrapsPayment;

    /**
     * Create the event listener.
     */
    public function __construct(
        private PaymentHelper $helper,
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PriceChanged $event): void
    {
        $parentModel = $this->bootstrap($event->parent, $this->helper);
        $survey = $parentModel->paymentSurvey;

        if ($parentModel->activePayment()->exists()) {
            return;
        }

        $defaultDeposit = config('payment.default_cash_deposit');

        if (Str::of($defaultDeposit)->endsWith('%')) {
            // Calculate deposit as percentage of total
            $percentage = Str::of($defaultDeposit)->beforeLast('%')->__toString();
            $deposit = floatval($percentage) / 100 * $this->helper->getTotalCost();
        } else {
            // Standard numeric deposit values
            $deposit = $defaultDeposit;
        }

        $survey->update([
            'cash_deposit' => $deposit
        ]);
    }
}
