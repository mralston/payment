<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Mralston\Payment\Enums\LookupField;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentLookupField;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Models\PaymentProvider;
use Mralston\Payment\Traits\BootstrapsPayment;

class FinanceController
{
    use BootstrapsPayment;

    public function __construct(
        private PaymentHelper $helper,
    ) {
        //
    }

    public function create(Request $request, int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        $offer = PaymentOffer::findOrFail($request->get('offerId'));

        return Inertia::render('Finance/Create', [
            'parentModel' => $parentModel,
            'survey' => $parentModel->paymentSurvey,
            'offer' => $offer,
            'totalCost' => $this->helper->getTotalCost(),
            'deposit' => $this->helper->getDeposit(),
            'companyDetails' => $this->helper->getCompanyDetails(),
            'lenders' => PaymentProvider::all(),
            'maritalStatuses' => PaymentLookupField::byIdentifier(LookupField::MARITAL_STATUS)
                ->paymentLookupValues,
            'employmentStatuses' => PaymentLookupField::byIdentifier(LookupField::EMPLOYMENT_STATUS)
                ->paymentLookupValues,
            'homeowners' => PaymentLookupField::byIdentifier(LookupField::HOMEOWNER)
                ->paymentLookupValues,
            'mortgages' => PaymentLookupField::byIdentifier(LookupField::MORTGAGE)
                ->paymentLookupValues,
            'britishCitizens' => PaymentLookupField::byIdentifier(LookupField::BRITISH_CITIZEN)
                ->paymentLookupValues,
        ])
            ->withViewData($this->helper->getViewData());
    }

    public function store(Request $request, int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);
        $survey = $parentModel->paymentSurvey;
        $offer = PaymentOffer::findOrFail($request->get('offerId'));

        // Update survey
        $survey->setCustomerProperty(0, 'maritalStatus', $request->get('maritalStatus'));
        $survey->setCustomerProperty(0, 'homeowner', $request->get('homeowner'));
        $survey->setCustomerProperty(0, 'mortgage', $request->get('mortgage'));
        $survey->setCustomerProperty(0, 'britishCitizen', $request->get('britishCitizen'));
        $survey->save();

        // TODO: Store the product if it doesn't already exist
        dump($request->all());

        // TODO: Create payment
        $payment = Payment::make()
            ->withSurvey($survey)
            ->withOffer($offer)
            ->setParent($parentModel)
            ->fill([
                'deposit' => $this->helper->getDeposit(),
                'eligible' => $request->get('eligible'),
                'gdpr_opt_in' => $request->get('gdprOptIn'),
                'read_terms_conditions' => $request->get('readTermsConditions'),
                'bank_account_number' => $request->get('accountNumber'),
                'bank_account_sort_code' => $request->get('sortCode'),
            ])
            ->save();

        dd($payment);

        // TODO: Submit payment to provider

        return redirect()
            ->route('payment.finance.show', [
                'parent' => $parent,
                'finance' => $payment
            ]);
    }

    public function show(int $parent, Payment $finance)
    {
        $payment = $finance;

        $parentModel = $this->bootstrap($parent, $this->helper);

        return Inertia::render('Finance/Show', [
            'parentModel' => $parentModel,
            'payment' => $payment,
        ])
            ->withViewData($this->helper->getViewData());
    }
}
