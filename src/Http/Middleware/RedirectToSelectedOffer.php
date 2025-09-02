<?php

namespace Mralston\Payment\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Interfaces\PaymentParentModel;
use Mralston\Payment\Traits\BootstrapsPayment;
use Symfony\Component\HttpFoundation\Response;

class RedirectToSelectedOffer
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

        if ($redirect = $this->redirectToSelectedOffer($request, $parentModel)) {
            return redirect($redirect);
        }

        return $next($request);
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
}
