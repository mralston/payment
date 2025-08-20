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
            'creditCheckConsent' => 'accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'eligible.accepted' => 'You must meet the eligiblity requirements.',
            'gdprOptIn.accepted' => 'You must agree to your personal data being used for the purposes of processing your application.',
            'readTermsConditions.accepted' => 'You must confirm you have read and understood the important information.',
            'creditCheckConsent.accepted' => 'You must consent to the identity and credit check being performed.',
        ];
    }
}
