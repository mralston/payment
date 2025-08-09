<?php

namespace Mralston\Payment\Services;

use Illuminate\Support\Facades\Log;

class PaymentCalculator
{
    /**
     * Calculates monthly payments for a loan, with or without a deferred payment period.
     *
     * @param float $amt The total loan amount.
     * @param float $apr The annual percentage rate.
     * @param int $term The loan term in months.
     * @param int $paymentDeferred The number of months the payment is deferred.
     * @return array|null An associative array of payment details or null if the term is invalid.
     */
    public function calculate(float $amt, float $apr, int $term, ?int $paymentDeferred = 0): ?array
    {
        $paymentDeferred ??= 0;

        if ($term <= 0) {
            return null;
        }

        // Calculate with a deferred period
        if ($paymentDeferred > 0) {
            $rate = $this->aprToRateV2($apr);

            $balanceAtEndOfDeferredPeriod = pow(1 + $rate, $paymentDeferred) * $amt;
            $balanceOnFirstDueDate = (1 + $rate * 5 / 365 * 12) * $balanceAtEndOfDeferredPeriod;

            $payment = ($rate * ($balanceOnFirstDueDate * pow(1 + $rate, $term))) / ((1 + $rate) * (pow(1 + $rate, $term) - 1));

            $finalPayment = ((($payment * (1 + $rate) * (pow(1 + $rate, $term - 1) - 1)) / $rate) - ($balanceOnFirstDueDate * pow(1 + $rate, $term - 1))) * -1;

            $totalPayable = $payment * ($term - 1) + $finalPayment;
            $interest = $totalPayable - $amt;

            return [
                'term' => $term,
                'firstPayment' => $payment,
                'monthlyPayment' => $payment,
                'finalPayment' => $finalPayment,
                'total' => $totalPayable,
                'apr' => $apr,
                'amt' => $amt,
                'interest' => $interest
            ];
        }

        // Calculate non-deferred

        $rate = $this->aprToRates($apr)['monthly'] / 100;
        $payment = 0;

        if ($apr == 0 || $rate == 0) {
            $payment = $amt / $term;
        } else {
            $calc = 1 / (1 + $rate);
            $payment = ($amt * ($calc - 1)) / ($calc * (pow($calc, $term) - 1));
        }

        $total = $payment * $term;
        $interest = $total - $amt;

        return [
            'term' => $term,
            'firstPayment' => $payment,
            'monthlyPayment' => $payment,
            'finalPayment' => $payment,
            'total' => round($total, 2),
            'apr' => $apr,
            'amt' => round($amt, 2),
            'interest' => $interest
        ];
    }

    /**
     * Converts an Annual Percentage Rate (APR) to a monthly rate using a simple formula.
     * This is specifically for the Ikano Bank calculations.
     *
     * @param float $apr The annual percentage rate.
     * @return float The monthly rate.
     */
    protected function aprToRateV2(float $apr): float
    {
        return pow(1 + $apr * 0.01, 1 / 12) - 1;
    }

    /**
     * Converts an APR to both monthly and annual rates using a more complex calculation.
     *
     * @param float $apr The annual percentage rate.
     * @return array An associative array with 'monthly' and 'annual' rates.
     */
    protected function aprToRates(float $apr): array
    {
        $apr = 1 + $apr / 100;
        $x = round(
            round(
                (
                    pow(
                        (
                            1 + (
                                (
                                    pow(
                                        $apr,
                                        (1 / 12)
                                    ) - 1
                                ) / 100
                            )
                        ),
                        12
                    ) - 1
                ) * 100 * 100 * 100
            ) / 100,
            1
        );

        return [
            'monthly' => round($x / 12 / 100, 6) * 100,
            'annual' => $x
        ];
    }
}
