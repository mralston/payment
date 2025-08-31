<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Mralston\Payment\Data\FinanceData;
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
            'paymentSurvey' => new PaymentSurvey()
                ->tap(function ($survey) use ($helper) {
                    $survey->customers = $helper->getCustomers();
                    $survey->addresses = [$helper->getAddress()];
                }),
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
                ...$request->except(
                    'basicQuestionsCompleted',
                    'leaseQuestionsCompleted',
                    'financeQuestionsCompleted',
                    'leaseResponses',
                    'financeResponses',
                    'redirect',
                    'creditCheckConsent',
                ),
                ...($request->boolean('basicQuestionsCompleted') ? ['basic_questions_completed' => '1'] : []),
                ...($request->boolean('leaseQuestionsCompleted') ? ['lease_questions_completed' => '1'] : []),
                ...($request->boolean('financeQuestionsCompleted') ? ['finance_questions_completed' => '1'] : []),
                ...($request->boolean('creditCheckConsent') ? ['credit_check_consent' => '1'] : []),
                'lease_responses' => $request->get('leaseResponses'),
                'finance_responses' => $request->get('financeResponses'),
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
            'employmentStatuses' => PaymentLookupField::byIdentifier(LookupField::EMPLOYMENT_STATUS)
                ->paymentLookupValues,
            'allowSkip' => true,
            'showBasicQuestions' => true,
        ])->withViewData($helper->getViewData());
    }

    public function lease(Request $request, int $parent, PaymentSurvey $survey, PaymentHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        $this->redirectToActivePayment($parentModel);

        return Inertia::render('Survey/Edit', [
            'parentModel' => $parentModel,
            'paymentSurvey' => $survey,
            'employmentStatuses' => PaymentLookupField::byIdentifier(LookupField::EMPLOYMENT_STATUS)
                ->paymentLookupValues,
            'title' => 'Lease Survey',
            'allowSkip' => false,
            'showBasicQuestions' => true,
            'redirect' => route('payment.lease.create', ['parent' => $parent, 'offerId' => $request->get('offerId')]),
            'basicIntroText' => 'We need you to answer these questions for your lease application.',
        ])->withViewData($helper->getViewData());
    }

    public function finance(Request $request, int $parent, PaymentSurvey $survey, PaymentHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        $this->redirectToActivePayment($parentModel);

        if (empty($survey->finance_responses)) {
            $survey->finance_responses = app(FinanceData::class);
        }

        return Inertia::render('Survey/Edit', [
            'parentModel' => $parentModel,
            'paymentSurvey' => $survey,
            'title' => 'Finance Survey',
            'financeResponses' => $survey->financeResponses ?? app(FinanceData::class),
            'maritalStatuses' => PaymentLookupField::byIdentifier(LookupField::MARITAL_STATUS)
                ->paymentLookupValues,
            'employmentStatuses' => PaymentLookupField::byIdentifier(LookupField::EMPLOYMENT_STATUS)
                ->paymentLookupValues,
            'residentialStatuses' => PaymentLookupField::byIdentifier(LookupField::RESIDENTIAL_STATUS)
                ->paymentLookupValues,
            'nationalities' => PaymentLookupField::byIdentifier(LookupField::NATIONALITY)
                ->paymentLookupValues,
            'bankruptOrIvas' => PaymentLookupField::byIdentifier(LookupField::BANKRUPT_OR_IVA)
                ->paymentLookupValues,
            'allowSkip' => false,
            'showBasicQuestions' => false,
            'showFinanceQuestions' => true,
            'redirect' => route('payment.finance.create', ['parent' => $parent, 'offerId' => $request->get('offerId')]),
        ])->withViewData($helper->getViewData());
    }

    public function update(SubmitSurveyRequest $request, int $parent, PaymentHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        $this->redirectToActivePayment($parentModel);

        $survey = $parentModel->paymentSurvey;

        $survey
            ->update([
                ...$request->except(
                    'basicQuestionsCompleted',
                    'leaseQuestionsCompleted',
                    'financeQuestionsCompleted',
                    'leaseResponses',
                    'financeResponses',
                    'redirect',
                    'creditCheckConsent',
                ),
                ...($request->boolean('basicQuestionsCompleted') ? ['basic_questions_completed' => '1'] : []),
                ...($request->boolean('leaseQuestionsCompleted') ? ['lease_questions_completed' => '1'] : []),
                ...($request->boolean('financeQuestionsCompleted') ? ['finance_questions_completed' => '1'] : []),
                ...($request->boolean('creditCheckConsent') ? ['credit_check_consent' => '1'] : []),
                'lease_responses' => $request->get('leaseResponses'),
                'finance_responses' => $request->get('financeResponses'),
            ]);

        // Clear offers if the survey was changed so that the options page is refreshed
        if ($survey->wasChanged()) {
            $parentModel->paymentOffers()->delete();
        }

        if ($request->get('redirect')) {
            return redirect($request->get('redirect'));
        }

        return redirect()
            ->route('payment.options', ['parent' => $parentModel]);
    }
}
