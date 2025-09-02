<?php

namespace Mralston\Payment\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Interfaces\PaymentParentModel;
use Mralston\Payment\Traits\BootstrapsPayment;
use Symfony\Component\HttpFoundation\Response;

class RedirectToActivePayment
{
    use BootstrapsPayment;

    public function __construct(
        protected PaymentHelper $helper
    ) {
        //
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $parentModel = $this->bootstrap($request->route('parent'), $this->helper);

        if ($redirect = $this->redirectToActivePayment($request, $parentModel)) {
            return redirect($redirect);
        }

        if ($redirect = $this->redirectToSelectedOffer($request, $parentModel)) {
            return redirect($redirect);
        }

        if ($redirect = $this->redirectIfNewPaymentProhibited($request, $parentModel)) {
            return redirect($redirect);
        }

        return $next($request);
    }

    protected function redirectToActivePayment(Request $request, PaymentParentModel $parentModel): ?string
    {
        // Check to see whether the parent has an active payment
        if (!empty($parentModel->activePayment)) {

            $type = $parentModel->activePayment?->paymentOffer->type ??
                $parentModel->activePayment->paymentProvider->paymentType->identifier;

            // Prevent redirecting to the same page
            if ($request->routeIs('payment.' . $type . '.show')) {
                return null;
            }

            // Redirect to the payment show page
            return route('payment.' . $type . '.show', [
                'parent' => $parentModel,
                $type => $parentModel->activePayment->id,
            ]);
        }

        return null;
    }

    protected function redirectToSelectedOffer(Request $request, PaymentParentModel $parentModel): ?string
    {
        // Check to see whether the parent has an active payment
        if (!empty($parentModel->selectedPaymentOffer)) {
            // Prevent redirecting to the same page
            if ($request->routeIs('payment.' . $parentModel->selectedPaymentOffer->type . '.create')) {
                return null;
            }

            // Redirect to the payment create page
            return route('payment.' . $parentModel->selectedPaymentOffer->type . '.create', [
                'parent' => $parentModel,
                'offerId' => $parentModel->selectedPaymentOffer->id,
            ]);
        }

        return null;
    }

    protected function redirectIfNewPaymentProhibited(Request $request, PaymentParentModel $parentModel): ?string
    {
        if (
            $parentModel
                ->payments()
                ->where('prevent_payment_changes', true)
                ->exists()
        ) {
            // Prevent redirecting to the same page
            if ($request->routeIs('payment.locked')) {
                return null;
            }

            // Redirect to the locked page
            return route('payment.locked', [
                'parent' => $parentModel
            ]);
        }

        return null;
    }
}
