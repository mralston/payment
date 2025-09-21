<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mralston\Payment\Observers\PaymentOfferObserver;


#[ObservedBy(PaymentOfferObserver::class)]
class PaymentOffer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payment_survey_id',
        'name',
        'type',
        'reference',
        'total_cost',
        'amount',
        'deposit',
        'payment_provider_id',
        'payment_product_id',
        'apr',
        'term',
        'deferred',
        'deferred_type',
        'upfront_payment',
        'first_payment',
        'monthly_payment',
        'final_payment',
        'minimum_payments',
        'total_payable',
        'status',
        'preapproval_id',
        'priority',
        'provider_application_id',
        'provider_offer_id',
        'small_print',
        'selected',
    ];

    protected $hidden = [
        'minimum_payments',
        'small_print',
    ];

    protected $appends = [
        'yearly_payments',
    ];

    public function casts(): array
    {
        return [
            'minimum_payments' => 'collection',
        ];
    }

    public function paymentProvider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class);
    }

    public function paymentSurvey(): BelongsTo
    {
        return $this->belongsTo(PaymentSurvey::class);
    }

    public function paymentProduct(): BelongsTo
    {
        return $this->belongsTo(PaymentProduct::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get the total payments for each year of the term.
     *
     * This attribute resolves the Pusher 10k payload limit by summarizing
     * the monthly payments into yearly totals on the server.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function yearlyPayments(): Attribute
    {
        return Attribute::get(
            function ($value, $attributes) {
                $term = $attributes['term'];
                $deferred = $attributes['deferred'] ?? 0;
                $monthlyPayments = [];

                // Scenario 1: Use the detailed minimum_payments array if available
                // Remove the upfront cost as this expressed separately
                if ($this->minimum_payments && $this->minimum_payments->isNotEmpty()) {
                    $monthlyPayments = $this
                        ->minimum_payments
                        ->map(function ($payment, $index) {
                            $payment = round((float) $payment, 2);

                            if ($index === 0 && $this->upfront_payment > 0) {
                                $payment = $payment - $this->upfront_payment;
                            }

                            return $payment;
                        })
                        ->values()
                        ->all();
                }

                // Scenario 2: Build the payment schedule from first/monthly/final payments
                else {
                    for ($month = 1; $month <= $term; $month++) {
                        if ($month === 1) {
                            $monthlyPayments[] = $attributes['first_payment'] ?? $attributes['monthly_payment'];
                        } elseif ($month === $term) {
                            $monthlyPayments[] = $attributes['final_payment'] ?? $attributes['monthly_payment'];
                        } else {
                            $monthlyPayments[] = $attributes['monthly_payment'];
                        }
                    }
                }

                // Prepend zero-payment months for any deferred period
                if ($deferred > 0) {
                    $deferredMonths = array_fill(0, $deferred, 0);
                    $monthlyPayments = array_merge($deferredMonths, $monthlyPayments);
                }

                // Chunk all monthly payments into groups of 12 for each year
                $yearlyChunks = array_chunk($monthlyPayments, 12);

                // Sum each chunk to get the total payment for that year
                return array_map('array_sum', $yearlyChunks);
            }
        );
    }
}
