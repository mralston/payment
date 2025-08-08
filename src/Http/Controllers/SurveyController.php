<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Mralston\Payment\Enums\LookupField;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Models\PaymentLookupField;
use Mralston\Payment\Models\PaymentSurvey;
use Mralston\Payment\Traits\BootstrapsPayment;

class SurveyController
{
    use BootstrapsPayment;

    public function create(int $parent, PaymentHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        // If there is already a survey, redirect to the existing survey
        if (!empty($parentModel->paymentSurvey)) {
            return redirect()->route('payment.surveys.edit', [
                'parent' => $parentModel,
                'survey' => $parentModel->paymentSurvey->id
            ]);
        }

        return Inertia::render('Survey/Edit', [
            'parentModel' => $parentModel,
            'customers' => $helper->getCustomers(),
            'addresses' => [$helper->getAddress()],
            'employmentStatuses' => PaymentLookupField::byIdentifier(LookupField::EMPLOYMENT_STATUS)
                ->paymentLookupValues,
        ])->withViewData($helper->getViewData());
    }

    public function store(Request $request, int $parent, PaymentHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        $parentModel->paymentSurvey()
            ->create($request->all());

        return redirect()
            ->route('payment.options', ['parent' => $parentModel]);
    }

    public function edit(int $parent, PaymentSurvey $survey, PaymentHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        return Inertia::render('Survey/Edit', [
            'parentModel' => $parentModel,
            'paymentSurvey' => $survey,
            'customers' => $survey->customers ?? [],
            'addresses' => $survey->addresses ?? [],
            'employmentStatuses' => PaymentLookupField::byIdentifier(LookupField::EMPLOYMENT_STATUS)
                ->paymentLookupValues,
        ])->withViewData($helper->getViewData());
    }

    public function update(Request $request, int $parent, PaymentHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        $parentModel->paymentSurvey()
            ->update($request->all());

        return redirect()
            ->route('payment.options', ['parent' => $parentModel]);
    }
}
