<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Mralston\Payment\Enums\LookupField;
use Mralston\Payment\Http\Requests\SubmitSurveyRequest;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Models\PaymentLookupField;
use Mralston\Payment\Models\PaymentSurvey;
use Mralston\Payment\Traits\BootstrapsPayment;
use Mralston\Payment\Traits\RedirectsOnActivePayment;

class SurveyController
{
    use BootstrapsPayment;
    use RedirectsOnActivePayment;

    public function create(int $parent, PaymentHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        $this->checkForActivePayment($parentModel);

        return Inertia::render('Survey/Edit', [
            'parentModel' => $parentModel,
            'customers' => $helper->getCustomers(),
            'addresses' => [$helper->getAddress()],
            'employmentStatuses' => PaymentLookupField::byIdentifier(LookupField::EMPLOYMENT_STATUS)
                ->paymentLookupValues,
        ])->withViewData($helper->getViewData());
    }

    public function store(SubmitSurveyRequest $request, int $parent, PaymentHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        $this->checkForActivePayment($parentModel);

        $parentModel->paymentSurvey()
            ->create($request->all());

        return redirect()
            ->route('payment.options', ['parent' => $parentModel]);
    }

    public function edit(int $parent, PaymentSurvey $survey, PaymentHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        $this->checkForActivePayment($parentModel);

        return Inertia::render('Survey/Edit', [
            'parentModel' => $parentModel,
            'paymentSurvey' => $survey,
            'customers' => $survey->customers ?? [],
            'addresses' => $survey->addresses ?? [],
            'employmentStatuses' => PaymentLookupField::byIdentifier(LookupField::EMPLOYMENT_STATUS)
                ->paymentLookupValues,
        ])->withViewData($helper->getViewData());
    }

    public function update(SubmitSurveyRequest $request, int $parent, PaymentHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        $this->checkForActivePayment($parentModel);

        $parentModel->paymentSurvey()
            ->update($request->all());

        return redirect()
            ->route('payment.options', ['parent' => $parentModel]);
    }
}
