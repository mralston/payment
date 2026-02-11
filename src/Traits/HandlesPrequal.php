<?php

namespace Mralston\Payment\Traits;

use Mralston\Payment\Data\PrequalPromiseData;
use Mralston\Payment\Data\PrequalData;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Models\PaymentProvider;
use Mralston\Payment\Models\PaymentSurvey;
use Illuminate\Support\Str;
use Mralston\Payment\Services\PaymentCalculator;
use Mralston\Payment\Events\OffersReceived;

trait HandlesPrequal
{
    public function runPrequal(
        string $provider,
        PaymentSurvey $survey,
        float $totalCost
    ): PrequalPromiseData|PrequalData
    {
        dispatch(function () use ($provider, $survey, $totalCost) {
            $helper = app(PaymentHelper::class)
                ->setParentModel($survey->parentable);

            $paymentProvider = PaymentProvider::byIdentifier($provider);

            $deposit = $survey->finance_deposit;
            $amount = $totalCost - $deposit;

            // See if there are already offers
            $offers = $survey
                ->paymentOffers()
                ->where('payment_provider_id', $paymentProvider->id)
                ->where('total_cost', $totalCost)
                ->where('amount', $amount)
                ->where('deposit', $deposit)
                ->where('selected', false)
                ->get();

            // If there aren't any offers...
            if ($offers->isEmpty()) {
                $products = $paymentProvider->paymentProducts;

                $reference = $helper->getReference() . '-' . Str::of(Str::random(5))->upper();

                $calculator = app(PaymentCalculator::class);

                // Store products to offers
                $offers = collect();
                
                $products->map(function ($product) use (
                    $offers,
                    $survey,
                    $paymentProvider,
                    $reference,
                    $calculator,
                    $totalCost,
                    $amount,
                    $deposit,
                ) {

                    $payments = $calculator->calculate($amount, $product->apr, $product->term, $product->deferred ?? 0);

                    $offers->push($survey->parentable
                        ->paymentOffers()
                        ->create([
                            'payment_survey_id' => $survey->id,
                            'payment_provider_id' => $paymentProvider->id,
                            'payment_product_id' => $product->id,
                            'name' => $product->name,
                            'type' => 'finance',
                            'reference' => $reference,
                            'total_cost' => $totalCost,
                            'amount' => $amount,
                            'deposit' => $deposit,
                            'apr' => $product->apr,
                            'term' => $product->term,
                            'deferred' => ($product->deferred ?? 0) > 0 ? ($product->deferred ?? 0) : null,
                            'deferred_type' => $product->deferred_type ?? null,
                            'first_payment' => $payments['firstPayment'],
                            'monthly_payment' => $payments['monthlyPayment'],
                            'final_payment' => $payments['finalPayment'],
                            'total_payable' => $payments['total'],
                            'status' => 'final',
                        ]));
                });
            }

            event(new OffersReceived(
                gateway: '\Mralston\Payment\Integrations\\' . $provider,
                type: 'finance',
                surveyId: $survey->id,
                offers: $offers,
            ));
        });

        return new PrequalPromiseData(
            gateway: '\Mralston\Payment\Integrations\\' . $provider,
            type: 'finance',
            surveyId: $survey->id,
        );
    }
}
