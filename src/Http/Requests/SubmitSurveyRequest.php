<?php

namespace Mralston\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mralston\Payment\Enums\LookupField;
use Mralston\Payment\Models\PaymentLookupField;
use Propaganistas\LaravelPhone\Rules\Phone;
use Propaganistas\LaravelPhone\PhoneNumber;

class SubmitSurveyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [];

        if ($this->boolean('basicQuestionsCompleted')) {
            $rules = [
                ...[
                    // Customer validation rules
                    'customers' => ['required', 'array'],
                    'customers.*.firstName' => ['required', 'string', 'max:255'],
                    'customers.*.middleName' => ['nullable', 'string', 'max:255'],
                    'customers.*.lastName' => ['required', 'string', 'max:255'],
                    'customers.*.email' => ['required', 'string', 'email', 'max:255'],
                    'customers.*.mobile' => ['required','string', 'numeric'],
                    'customers.*.landline' => ['nullable', 'string', 'numeric'],
                    'customers.*.dateOfBirth' => ['required', 'date'],
                    'customers.*.grossAnnualIncome' => ['required', 'numeric'],
                    'customers.*.netMonthlyIncome' => ['required', 'numeric'],
                    'customers.*.dependants' => ['required', 'numeric'],
                    'customers.*.employmentStatus' => ['required', Rule::in(
                        PaymentLookupField::byIdentifier(LookupField::EMPLOYMENT_STATUS)
                            ?->paymentLookupValues
                            ?->pluck('value')
                    )],

                    // Address validation rules
                    'addresses' => ['required', 'array'],
                    'addresses.*.houseNumber' => ['required', 'string', 'max:255'],
                    'addresses.*.street' => ['required', 'string', 'max:255'],
                    'addresses.*.town' => ['required', 'string', 'max:255'],
                    'addresses.*.county' => ['nullable', 'string', 'max:255'],
                    'addresses.*.postCode' => ['required', 'string', 'max:255'],
                    'addresses.*.dateMovedIn' => ['required', 'date'],
                    'addresses.*.uprn' => ['required'],
                ]
            ];
        }

        if ($this->boolean('leaseQuestionsCompleted')) {
            $rules = [
                ...[

                ]
            ];
        }

        if ($this->boolean('financeQuestionsCompleted')) {
            $rules = [
                ...[
                    'customers.*.maritalStatus' => 'required',
                    'customers.*.residentialStatus' => 'required',
                    'customers.*.nationality' => 'required',
                    'customers.*.bankruptOrIva' => 'required',
                    'customers.*.employmentStatus' => ['required', Rule::in(
                        PaymentLookupField::byIdentifier(LookupField::EMPLOYMENT_STATUS)
                            ?->paymentLookupValues
                            ?->pluck('value')
                    )],

                    'financeResponses.occupation' => ['required', 'string'],
                    'financeResponses.employerName' => ['required', 'string'],
                    'financeResponses.dateStartedEmployment' => ['required', 'date'],

                    'financeResponses.employerAddress.address1' => ['required', 'string', 'max:255'],
                    'financeResponses.employerAddress.address2' => ['nullable', 'string', 'max:255'],
                    'financeResponses.employerAddress.town' => ['required', 'string', 'max:255'],
                    'financeResponses.employerAddress.county' => ['nullable', 'string', 'max:255'],
                    'financeResponses.employerAddress.postCode' => ['required', 'string', 'max:255'],
                    'financeResponses.employerAddress.uprn' => ['required'],

                    'financeResponses.bankAccount.bankName' => ['required', 'string', 'max:255'],
                    'financeResponses.bankAccount.accountName' => ['required', 'string', 'max:255'],
                    'financeResponses.bankAccount.accountNumber' => ['required', 'numeric', 'digits:8'],
                    'financeResponses.bankAccount.sortCode' => ['required', 'string', 'regex:/^\d{6}$|^\d{2}-\d{2}-\d{2}$/'],
                ]
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            // Customer validation messages
            'customers.required' => 'You must enter at least one customer.',
            'customers.*.firstName.required' => 'You must enter a first name.',
            'customers.*.lastName.required' => 'You must enter a last name.',
            'customers.*.email.required' => 'You must enter an email address.',
            'customers.*.email.email' => 'The email address you entered is not valid.',
            'customers.*.mobile.required' => 'You must enter a mobile number.',
            'customers.*.mobile.numeric' => 'The mobile number you entered is not valid.',
            'customers.*.landline.numeric' => 'The mobile number you entered is not valid.',
            'customers.*.dateOfBirth.required' => 'You must enter a date of birth.',
            'customers.*.dateOfBirth.date' => 'The date of birth is not a valid date.',
            'customers.*.grossAnnualIncome.required' => 'You must enter a gross annual income.',
            'customers.*.grossAnnualIncome.numeric' => 'The gross annual income must be a number.',
            'customers.*.netMonthlyIncome.required' => 'You must enter a net monthly income.',
            'customers.*.netMonthlyIncome.numeric' => 'The net monthly income must be a number.',
            'customers.*.employmentStatus.required' => 'You must select an employment status.',
            'customers.*.employmentStatus.in' => 'The selected employment status is not valid.',
            'customers.*.dependants.required' => 'You must specify how many dependants you have.',
            'customers.*.dependants.numeric' => 'The number of dependants must be a number.',

            // Address validation messages
            'addresses.required' => 'You must enter at least one address.',
            'addresses.*.houseNumber.required' => 'You must enter a house number.',
            'addresses.*.street.required' => 'You must enter a street name.',
            'addresses.*.address1.required' => 'You must enter the first line of the address.',
            'addresses.*.town.required' => 'You must enter a town.',
            'addresses.*.postCode.required' => 'You must enter a post code.',
            'addresses.*.dateMovedIn.required' => 'You must enter a date moved in.',
            'addresses.*.dateMovedIn.date' => 'The date moved in is not a valid date.',
            'addresses.*.uprn.required' => 'You must use the post code lookup button to select an exact address.',

            'customers.*.maritalStatus.required' => 'You must select your marital status.',
            'customers.*.residentialStatus.required' => 'You must select your residential status.',
            'customers.*.nationality.required' => 'You must select your nationality.',
            'customers.*.bankruptOrIva.required' => 'You must select your bankrupt or IVA status.',

            'financeResponses.occupation.required' => 'You must enter your occupation.',
            'financeResponses.employerName.required' => 'You must enter the name of your employer.',
            'financeResponses.dateStartedEmployment.required' => 'You must enter the date you started employment.',
            'financeResponses.employerAddress.address1.required' => 'You must enter the first line.',
            'financeResponses.employerAddress.town.required' => 'You must enter the town.',
            'financeResponses.employerAddress.postCode.required' => 'You must enter the post code.',
            'financeResponses.employerAddress.uprn.required' => 'You must use the post code lookup button to select an exact address.',

            'financeResponses.bankAccount.bankName.required' => 'You must enter the name of the bank.',
            'financeResponses.bankAccount.accountName.required' => 'You must enter the name of the account holder.',
            'financeResponses.bankAccount.accountNumber.required' => 'You must enter the account number.',
            'financeResponses.bankAccount.accountNumber.numeric' => 'The account number must be numeric.',
            'financeResponses.bankAccount.accountNumber.digits' => 'The account number must be 8 digits.',
            'financeResponses.bankAccount.sortCode.required' => 'You must enter the sort code.',
            'financeResponses.bankAccount.sortCode.regex' => 'The sort code must be in the format 123456 or 12-34-56.',

        ];
    }
}
