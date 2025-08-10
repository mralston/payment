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

        $this->redirectToActivePayment($parentModel);

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
            'allowSkip' => true,
            'showBasicQuestions' => true,
        ])->withViewData($helper->getViewData());
    }

    public function store(SubmitSurveyRequest $request, int $parent, PaymentHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        $this->redirectToActivePayment($parentModel);

        $parentModel->paymentSurvey()
            ->create([
                ...$request->except('basicQuestionsCompleted', 'leaseQuestionsCompleted', 'financeQuestionsCompleted'),
                ...($request->boolean('basicQuestionsCompleted') ? ['basic_questions_completed' => '1'] : []),
                ...($request->boolean('leaseQuestionsCompleted') ? ['lease_questions_completed' => '1'] : []),
                ...($request->boolean('financeQuestionsCompleted') ? ['finance_questions_completed' => '1'] : []),
            ]);

        return redirect()
            ->route('payment.options', ['parent' => $parentModel]);
    }

    public function edit(int $parent, PaymentSurvey $survey, PaymentHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        $this->redirectToActivePayment($parentModel);

        return Inertia::render('Survey/Edit', [
            'parentModel' => $parentModel,
            'paymentSurvey' => $survey,
            'customers' => $survey->customers ?? [],
            'addresses' => $survey->addresses ?? [],
            'employmentStatuses' => PaymentLookupField::byIdentifier(LookupField::EMPLOYMENT_STATUS)
                ->paymentLookupValues,
            'allowSkip' => true,
            'showBasicQuestions' => true,
        ])->withViewData($helper->getViewData());
    }

    public function finance(int $parent, PaymentSurvey $survey, PaymentHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        $this->redirectToActivePayment($parentModel);

        return Inertia::render('Survey/Edit', [
            'parentModel' => $parentModel,
            'paymentSurvey' => $survey,
            'customers' => $survey->customers ?? [],
            'addresses' => $survey->addresses ?? [],
            'employmentStatuses' => PaymentLookupField::byIdentifier(LookupField::EMPLOYMENT_STATUS)
                ->paymentLookupValues,
            'allowSkip' => false,
            'showBasicQuestions' => false,
            'showFinanceQuestions' => true,
        ])->withViewData($helper->getViewData());
    }

    public function update(SubmitSurveyRequest $request, int $parent, PaymentHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        $this->redirectToActivePayment($parentModel);

        $parentModel->paymentSurvey()
            ->update([
                ...$request->except('basicQuestionsCompleted', 'leaseQuestionsCompleted', 'financeQuestionsCompleted'),
                ...($request->boolean('basicQuestionsCompleted') ? ['basic_questions_completed' => '1'] : []),
                ...($request->boolean('leaseQuestionsCompleted') ? ['lease_questions_completed' => '1'] : []),
                ...($request->boolean('financeQuestionsCompleted') ? ['finance_questions_completed' => '1'] : []),
            ]);

        return redirect()
            ->route('payment.options', ['parent' => $parentModel]);
    }
}
