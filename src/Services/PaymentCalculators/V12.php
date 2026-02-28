<?php

namespace Mralston\Payment\Services\PaymentCalculators;

use Carbon\Carbon;
use Mralston\Payment\Models\PaymentProduct;

class V12
{
    public function calculate(float $amount, PaymentProduct $product): array
    {
        $apr = (float) $product->apr;
        $monthlyRate = (float) $product->monthly_rate;
        $months = (float) $product->term;
        $serviceFee = (float) $product->service_fee;
        $deferredPeriod = (int) $product->deferred;
        $documentFee = (float) $product->document_fee;
        $documentFeePercentage = (float) $product->document_fee_percentage;
        $documentFeeMinimum = (float) $product->document_fee_minimum;
        $documentFeeMaximum = (float) $product->document_fee_maximum;
        $documentFeeCollectionMonth = (int) $product->document_fee_collection_month;

        $totalDocumentFee = $documentFee + ($amount * $documentFeePercentage);

        if ($documentFeeMinimum > 0 && $totalDocumentFee < $documentFeeMinimum) {
            $totalDocumentFee = $documentFeeMinimum;
        }
        if ($documentFeeMaximum > 0 && $totalDocumentFee > $documentFeeMaximum) {
            $totalDocumentFee = $documentFeeMaximum;
        }

        if ($monthlyRate == 0) {
            $monthlyPayment = round(($amount / $months), 2);

            if ($monthlyPayment * $months < $amount) {
                $monthlyPayment += 0.01;
            }

            $finalPayment = round($amount - ($monthlyPayment * ($months - 1)), 2);
            $apr = 0;
        } else {
            $yield = pow(($apr / 100) + 1, (1.00 / 12));
            $pv = $amount - $serviceFee;

            if ($deferredPeriod > 1) {
                $pv = ($pv * pow($yield, ($deferredPeriod - 1)));
            }

            $monthlyPayment = ceil(((0 - $pv / ((pow($yield, 0 - $months) - 1) / ($yield - 1)))) * 100) / 100;
            $finalPayment = $monthlyPayment;
            $apr = $this->calculateApr(($amount - $serviceFee), $monthlyPayment, $deferredPeriod, $months);
        }

        if ($totalDocumentFee > 0) {
            $apr = $this->calculateAprFromIrr($amount, $monthlyPayment, $months, $totalDocumentFee, $documentFeeCollectionMonth);
        }

        $balancePayable = ($monthlyPayment * ($months - 1)) + $finalPayment;
        $interest = $balancePayable - $amount;
        $totalRepayable = $balancePayable + $serviceFee + $totalDocumentFee;

        return [
            'term' => (int) $months,
            'firstPayment' => (float) number_format($monthlyPayment, 2, '.', ''),
            'monthlyPayment' => (float) number_format($monthlyPayment, 2, '.', ''),
            'finalPayment' => (float) number_format($finalPayment, 2, '.', ''),
            'total' => (float) number_format($totalRepayable, 2, '.', ''),
            'apr' => (float) number_format($apr, 2, '.', ''),
            'amt' => (float) number_format($amount, 2, '.', ''),
            'interest' => (float) number_format($interest, 2, '.', ''),
        ];
    }

    protected function calculateApr(float $loan, float $instalment, int $deferred, float $term): float
    {
        $high = 200.0;
        $low = 0.0;
        $result = 0.0;

        $n = ($deferred > 1) ? ($term + $deferred + 1) : ($term + 1);

        for ($x = 0; $x < 20; $x++) {
            $result = ($high + $low) / 2;
            $j = pow((1.0000 + $result / 100.0000), (1.0000 / 12.0000));
            $q = 1.0000 / $j;

            if ($deferred < 1) {
                $y = ($instalment * (1.0000 - pow($q, $n))) / (1 - $q) - $instalment;
                $z = 0.00;
            } else {
                $y = ($instalment * (1.0000 - pow($q, $n - 1))) / (1 - $q) - $instalment;
                $z = ($instalment * (1.0000 - pow($q, $deferred))) / (1 - $q) - $instalment;
            }

            if (($y - $z) < $loan) {
                $high = $result;
            } else {
                $low = $result;
            }
        }

        return $result;
    }

    protected function XIRR(array $values, array $dates, float $guess = 0.1): float|string
    {
        $irrResult = function (array $values, array $dates, float $rate) {
            $r = $rate + 1;
            $result = $values[0];
            $startDate = Carbon::parse($dates[0]);
            for ($i = 1; $i < count($values); $i++) {
                $diff = $startDate->diffInDays(Carbon::parse($dates[$i]));
                $result += $values[$i] / pow($r, $diff / 365);
            }
            return $result;
        };

        $irrResultDeriv = function (array $values, array $dates, float $rate) {
            $r = $rate + 1;
            $result = 0;
            $startDate = Carbon::parse($dates[0]);
            for ($i = 1; $i < count($values); $i++) {
                $diff = $startDate->diffInDays(Carbon::parse($dates[$i]));
                $frac = $diff / 365;
                $result -= $frac * $values[$i] / pow($r, $frac + 1);
            }
            return $result;
        };

        $positive = false;
        $negative = false;
        foreach ($values as $value) {
            if ($value > 0) $positive = true;
            if ($value < 0) $negative = true;
        }

        if (!$positive || !$negative) return '#NUM!';

        $resultRate = $guess;
        $epsMax = 1e-10;
        $iterMax = 50;

        $iteration = 0;
        do {
            $resultValue = $irrResult($values, dates: $dates, rate: $resultRate);
            $derivative = $irrResultDeriv($values, $dates, $resultRate);
            if ($derivative == 0) break;
            $newRate = $resultRate - $resultValue / $derivative;
            $epsRate = abs($newRate - $resultRate);
            $resultRate = $newRate;
            $contLoop = ($epsRate > $epsMax) && (abs($resultValue) > $epsMax);
        } while ($contLoop && (++$iteration < $iterMax));

        if ($iteration >= $iterMax) {
            return '#NUM!';
        }

        return $resultRate;
    }

    protected function calculateAprFromIrr(float $loan, float $monthlyInstalment, float $loanTerm, float $documentFee, int $documentFeeCollectionMonth): float
    {
        $startDate = Carbon::now();
        $incomeTable = [];
        $dateTable = [];

        if ($documentFeeCollectionMonth === 0) {
            $incomeTable[] = ($loan * -1) + $documentFee;
        } else {
            $incomeTable[] = $loan * -1;
        }
        $dateTable[] = $startDate->toDateTimeString();

        for ($i = 1; $i <= $loanTerm; $i++) {
            $nextDate = $startDate->copy()->addMonths($i);
            $dateTable[] = $nextDate->toDateTimeString();

            if (($i - 1) === $documentFeeCollectionMonth && $documentFeeCollectionMonth > 0) {
                $incomeTable[] = $monthlyInstalment + $documentFee;
            } else {
                $incomeTable[] = $monthlyInstalment;
            }
        }

        $r = $this->XIRR($incomeTable, $dateTable, 0.1);

        if ($r === '#NUM!') {
            return 0.0;
        }

        return round((float) $r * 100, 2);
    }
}
