<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Mralston\Payment\Data\CancellationData;
use Mralston\Payment\Events\PaymentCancelled;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Interfaces\PaymentParentModel;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Models\PaymentProvider;
use Mralston\Payment\Models\PaymentStatus;
use Mralston\Payment\Models\PaymentSurvey;
use Mralston\Payment\Services\PaymentService;
use Mralston\Payment\Traits\BootstrapsPayment;

class PaymentOptionsController
{
    use BootstrapsPayment;

    public function __construct(
        private PaymentHelper $helper,
        private PaymentService $paymentService,
    ) {
        //
    }

    public function options(int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        $survey = $parentModel->paymentSurvey ?? $parentModel->paymentSurvey()->create([
            'customers' => $this->helper->getCustomers(),
            'addresses' => [$this->helper->getAddress()],
        ]);

        $survey = $this->setDefaultDeposits($survey, $parentModel);

        return Inertia::render('Payment/Options', [
            'parentModel' => $parentModel,
            'survey' => $survey->load([
                'paymentOffers' => fn ($query) => $query->where('selected', false),
                'paymentOffers.paymentProvider',
            ]),
            'customers' => $this->helper->getCustomers(),
            'totalCost' => $this->helper->getTotalCost(),
            'leaseMoreInfoContent' => $this->helper->getLeaseContent(),
            'paymentProviders' => PaymentProvider::all(),
            'systemSavings' => $this->helper->getSystemSavings(),
        ])->withViewData($this->helper->getViewData());
    }

    public function changeDeposit(Request $request, int $parent, string $paymentType)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        $survey = $parentModel->paymentSurvey;

        $survey->{$paymentType . '_deposit'} = $request->input('deposit');
        $survey->save();

        // Nobble existing offers to force a full re-qual. Not very elegant, but it works.
        // TODO: Only re-qual affected payment method
        $parentModel->paymentOffers()->delete();

        return redirect()
            ->route('payment.options', ['parent' => $parent]);
    }

    public function select(Request $request, int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        $survey = $parentModel->paymentSurvey;

        if ($request->input('paymentType') == 'cash' && $request->input('offerId') == 0) {
            // Create cash offer and select it
            $offer = $parentModel
                ->paymentOffers()
                ->updateOrCreate([
                    'payment_provider_id' => PaymentProvider::byIdentifier('cash')?->id,
                    'payment_survey_id' => $parentModel->paymentSurvey->id,
                ],[
                    'name' => 'Cash',
                    'type' => 'cash',
                    'reference' => $this->helper->getReference() . '-' . Str::of(Str::random(5))->upper(),
                    'total_cost' => $this->helper->getTotalCost(),
                    'amount' => $this->helper->getTotalCost() - $survey->cash_deposit,
                    'deposit' => $survey->cash_deposit,
                    'first_payment' => $survey->cash_deposit,
                    'final_payment' => $this->helper->getTotalCost() - $survey->cash_deposit,
                    'total_payable' => $this->helper->getTotalCost(),
                    'status' => 'final',
                    'selected' => true,
                ]);
        } else {
            // Select the specified finance/lease offer
            $offer = PaymentOffer::findOrFail($request->get('offerId'));
            $offer->update([
                'selected' => true,
            ]);
        }

        // Unselect other offers
        $parentModel
            ->paymentOffers()
            ->where('id', '!=', $offer->id)
            ->update([
                'selected' => false,
            ]);

        return redirect()
            ->route('payment.' . $request->input('paymentType') . '.create', [
                'parent' => $parent,
                'offerId' => $offer->id,
            ]);
    }

    public function unselect(Request $request, int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        // Unselect all offers
        $parentModel
            ->paymentOffers()
            ->update([
                'selected' => false,
            ]);

        return redirect()
            ->route('payment.options', ['parent' => $parent]);
    }

    private function setDefaultDeposits(PaymentSurvey $survey, PaymentParentModel $parentModel): PaymentSurvey
    {
        collect(['cash', 'finance', 'lease'])
            ->each(function ($paymentType) use ($survey, $parentModel) {
                if (is_null($survey->{$paymentType . '_deposit'})) {

                    $defaultDeposit = config('payment.default_' . $paymentType . '_deposit');

                    if (Str::of($defaultDeposit)->endsWith('%')) {
                        // Calculate deposit as percentage of total
                        $percentage = Str::of($defaultDeposit)->beforeLast('%')->__toString();
                        $deposit = floatval($percentage) / 100 * $this->helper->getTotalCost();
                    } else {
                        // Standard numeric deposit values
                        $deposit = $defaultDeposit;
                    }

                    $survey->{$paymentType . '_deposit'} = $deposit;
                }
            });

        $survey->save();

        return $survey;
    }
}
