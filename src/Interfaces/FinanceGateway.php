<?php

namespace Mralston\Payment\Interfaces;

use Illuminate\Support\Collection;
use Mralston\Payment\Data\PrequalData;
use Mralston\Payment\Data\PrequalPromiseData;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentSurvey;

interface FinanceGateway
{
    public function apply(Payment $payment): Payment;

    public function cancel(Payment $payment): bool;

    public function pollStatus(Payment $payment): array;

    public function calculatePayments(int $loanAmount, float $apr, int $loanTerm, ?int $deferredPeriod = null): array;

    public function financeProducts(): Collection;
}
