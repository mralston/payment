<?php

namespace Mralston\Payment\Rules;

use Illuminate\Database\Eloquent\Builder;
use Mralston\Payment\Enums\PaymentStatus;
use Mralston\Payment\Enums\PaymentProvider;
use Mralston\Payment\Interfaces\OfferVisibilityRule;
use Mralston\Payment\Interfaces\PaymentParentModel;
use Illuminate\Database\Eloquent\Relations\Relation;

class HometreeRule implements OfferVisibilityRule
{
    public function applyVisibilityConstraints(
        Builder|Relation $query,
        PaymentParentModel $parent
    ): void {
        $hasFinalDeclinedPayment = $parent
            ->payments()
            ->whereHas('paymentProvider', function ($query) {
                $query->where('identifier', PaymentProvider::HOMETREE);
            })
            ->whereHas('paymentStatus', function ($query) {
                $query->where('identifier', PaymentStatus::HOMETREE_FINAL_DECLINED);
            })->first();

        if ($hasFinalDeclinedPayment) {
            $query->whereHas('paymentProvider', function ($query) {
                $query->where('identifier', '!=', PaymentProvider::HOMETREE);
            });
        }
    }
}
