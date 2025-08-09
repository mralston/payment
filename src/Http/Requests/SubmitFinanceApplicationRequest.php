<?php

namespace Mralston\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitFinanceApplicationRequest extends FormRequest
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
            'eligible' => 'accepted',
            'gdprOptIn' => 'accepted',
            'readTermsConditions' => 'accepted',
            'maritalStatus' => 'required',
            'residentialStatus' => 'required',
            'nationality' => 'required',
            'accountNumber' => 'required|numeric|digits:8',
            'sortCode' => [
                'required',
                'regex:/^\d{6}$|^\d{2}-\d{2}-\d{2}$/',
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'eligible.accepted' => 'You must meet the eligiblity requirements.',
            'gdprOptIn.accepted' => 'You must agree to your personal data being used for the purposes of processing your application.',
            'readTermsConditions.accepted' => 'You must confirm you have read and understood the important information.',
            'maritalStatus.required' => 'You must select your marital status.',
            'residentialStatus.required' => 'You must select your residential status.',
            'nationality.required' => 'You must select your nationality.',
            'accountNumber.required' => 'You must enter your account number.',
            'accountNumber.numeric' => 'Your account number must be numeric.',
            'accountNumber.digits' => 'Your account number must be 8 digits.',
            'sortCode.required' => 'You must enter your sort code.',
            'sortCode.regex' => 'Your sort code must be in the format 123456 or 12-34-56.',
        ];
    }
}
