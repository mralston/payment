<?php

namespace Mralston\Payment\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Interfaces\PaymentParentModel;
use Mralston\Payment\Traits\BootstrapsPayment;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNewPaymentProhibited
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

        if ($redirect = $this->redirectIfNewPaymentProhibited($request, $parentModel)) {
            return redirect($redirect);
        }

        return $next($request);
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
