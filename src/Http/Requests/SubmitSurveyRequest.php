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
        return [
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
            'addresses.*.address1' => ['nullable', 'string', 'max:255'],
            'addresses.*.address2' => ['nullable', 'string', 'max:255'],
            'addresses.*.town' => ['required', 'string', 'max:255'],
            'addresses.*.county' => ['required', 'string', 'max:255'],
            'addresses.*.postCode' => ['required', 'string', 'max:255'],
            'addresses.*.dateMovedIn' => ['required', 'date'],
        ];
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
            'addresses.*.county.required' => 'You must enter a county.',
            'addresses.*.postCode.required' => 'You must enter a post code.',
            'addresses.*.dateMovedIn.required' => 'You must enter a date moved in.',
            'addresses.*.dateMovedIn.date' => 'The date moved in is not a valid date.',
        ];
    }
}
