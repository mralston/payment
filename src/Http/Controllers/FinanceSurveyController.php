<?php

namespace Mralston\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Mralston\Finance\Interfaces\FinanceHelper;
use Mralston\Finance\Models\FinanceSurvey;
use Mralston\Finance\Traits\BootstrapsFinance;

class FinanceSurveyController
{
    use BootstrapsFinance;

    public function create(int $parent, FinanceHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        // If there is already a survey, redirect to the existing survey
        if (!empty($parentModel->financeSurvey)) {
            return redirect()->route('finance.surveys.edit', [
                'parent' => $parentModel,
                'survey' => $parentModel->financeSurvey->id
            ]);
        }

        return Inertia::render('Survey/Edit', [
            'parentModel' => $parentModel,
            'customers' => $helper->getCustomers(),
            'addresses' => [$helper->getAddress()]
        ])->withViewData($helper->getViewData());
    }

    public function store(Request $request, int $parent, FinanceHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        $parentModel->financeSurvey()
            ->create($request->all());

        return redirect()
            ->route('finance.choose-method', ['parent' => $parentModel]);
    }

    public function edit(int $parent, FinanceSurvey $survey, FinanceHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        return Inertia::render('Survey/Edit', [
            'parentModel' => $parentModel,
            'financeSurvey' => $survey,
            'customers' => $survey->customers ?? [],
            'addresses' => $survey->addresses ?? [],
        ])->withViewData($helper->getViewData());
    }

    public function update(Request $request, int $parent, FinanceHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        $parentModel->financeSurvey()
            ->update($request->all());

        return redirect()
            ->route('finance.choose-method', ['parent' => $parentModel]);
    }
}
