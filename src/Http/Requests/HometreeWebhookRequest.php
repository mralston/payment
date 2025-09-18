<?php

namespace Mralston\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HometreeWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $payload = null;

        // Attempt to parse JSON body explicitly (covers raw JSON, including strings)
        $raw = $this->getContent();
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $payload = $decoded;
            }
        }

        // If not JSON, fall back to standard input array (e.g., form-encoded)
        if ($payload === null) {
            $payload = $this->all();
        }

        // Normalize to an array of records under the key 'records'
        if (is_array($payload)) {
            $isAssoc = array_keys($payload) !== range(0, count($payload) - 1);
            $records = $isAssoc ? [$payload] : $payload;
        } else {
            $records = null; // will fail validation
        }

        $this->merge(['records' => $records]);
    }

    public function rules(): array
    {
        // Require an array of records; each record must include the used keys.
        return [
            'records' => ['required', 'array', 'min:1'],

            'records.*' => ['required', 'array'],
            'records.*.htf-quote-id' => ['required'],
            'records.*.client-application-reference' => ['nullable'],
            'records.*.customer-full-name' => ['required', 'string'],

            'records.*.customer-address-line-1' => ['nullable', 'string'],
            'records.*.customer-address-line-2' => ['nullable', 'string'],
            'records.*.customer-address-line-3' => ['nullable', 'string'],
            'records.*.customer-postcode' => ['nullable', 'string'],
            'records.*.customer-udprn' => ['nullable'],
            'records.*.customer-uprn' => ['nullable'],

            'records.*.application-submitted-timestamp' => ['nullable', 'string'],
            'records.*.application-complete-timestamp' => ['nullable', 'string'],
            'records.*.application-created-timestamp' => ['nullable', 'string'],
            'records.*.application-price' => ['nullable', 'string'],
            'records.*.upfront-payment-amount' => ['nullable', 'string'],
            'records.*.monthly-payment-amount' => ['nullable', 'string'],
            'records.*.account-term' => ['nullable'],
            'records.*.total-payable' => ['nullable', 'string'],
            'records.*.application-status' => ['required', 'string'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        return $data['records'] ?? [];
    }
}
