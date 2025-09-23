<?php

namespace Mralston\Payment\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentProvider;
use Mralston\Payment\Models\PaymentStatus;
use Mralston\Payment\Models\PaymentType;
use Illuminate\Support\Facades\Log;

class HometreeService
{
    public function handleWebhook(Collection $records)
    {
        if ($records->isEmpty()) {
            // Nothing to process; likely validation rejected payload upstream.
            return;
        }

        $hometreeLender = PaymentProvider::firstWhere('identifier', 'hometree');
        $hometreePaymentType = PaymentType::firstWhere('i dentifier', 'lease');

        Payment::withoutEvents(function() use ($records, $hometreeLender, $hometreePaymentType) {
            Payment::withoutTimestamps(function () use ($hometreeLender, $records, $hometreePaymentType) {
                $records->each(function($record) use ($hometreeLender, $hometreePaymentType) {
                    $payment = Payment::firstOrNew([
                        'payment_provider_id' => $hometreeLender->id,
                        'provider_foreign_id' => $record['application-id'],
                    ]);

                    $payment->uuid = $payment->uuid ?? Str::uuid();
                    $payment->reference = $record['client-application-reference'] ?? null;
                    $payment->parentable_type = Str::of(config('payment.parent_model'))->ltrim('\\');

                    if (! $payment->exists || is_null($payment->parentable_id)) {
                        $payment->parentable_id = $this->integerOrNull($record['client-application-reference'] ?? null);
                    }

                    $payment->payment_type_id = $hometreePaymentType->id;
                    $payment->first_name = $this->extractFirstNameFromString($record['customer-full-name']);
                    $payment->middle_name = $this->extractMiddleNameFromString($record['customer-full-name']);
                    $payment->last_name = $this->extractLastNameFromString($record['customer-full-name']);
                    $payment->addresses = [[
                        'address1' => $record['customer-address-line-1'],
                        'address2' => $record['customer-address-line-2'],
                        'town' => $record['customer-address-line-3'],
                        'postCode' => $record['customer-postcode'],
                        'udprn' => $this->integerOrNull($record['customer-udprn']),
                        'uprn' => $this->integerOrNull($record['customer-uprn']),
                    ]];
                    $payment->submitted_at = $record['application-submitted-timestamp'];
                    $payment->decision_received_at = $record['application-complete-timestamp'];
                    $payment->amount = !blank($record['application-price']) ? Str::of($record['application-price'])->replace([',', '£'], '')->toFloat() : null;
                    $payment->payment_provider_id = $hometreeLender->id;
                    $payment->first_payment = !blank($record['upfront-payment-amount']) ? Str::of($record['upfront-payment-amount'])->replace([',', '£'], '')->toFloat() : null;
                    $payment->monthly_payment = !blank($record['monthly-payment-amount']) ? Str::of($record['monthly-payment-amount'])->replace([',', '£'], '')->toFloat() : null;
                    $payment->term = !blank($record['account-term']) ? $record['account-term'] * 12 : null;
                    $payment->total_payable = !blank($record['total-payable']) ? Str::of($record['total-payable'])->replace([',', '£'], '')->toFloat() : null;
                    $payment->payment_status_id = PaymentStatus::firstWhere('identifier', $this->translateStatus($record['application-status']))->id;
                    $payment->created_at = $record['application-created-timestamp'];
                    $payment->was_referred = false;

                    // Force-fill fields that should always be refreshed on each webhook
                    $payment->forceFill([
                        'decision_received_at' => $record['application-complete-timestamp'],
                        'term' => !blank($record['account-term']) ? $record['account-term'] * 12 : null,
                        'first_payment' => !blank($record['upfront-payment-amount']) ? Str::of($record['upfront-payment-amount'])->replace([',', '£'], '')->toFloat() : null,
                        'monthly_payment' => !blank($record['monthly-payment-amount']) ? Str::of($record['monthly-payment-amount'])->replace([',', '£'], '')->toFloat() : null,
                        'total_payable' => !blank($record['total-payable']) ? Str::of($record['total-payable'])->replace([',', '£'], '')->toFloat() : null,
                        'payment_status_id' => PaymentStatus::firstWhere('identifier', $this->translateStatus($record['application-status']))->id,
                        'was_referred' => $this->translateStatus($record['application-status']) == 'referred',
                        'created_at' => $record['application-created-timestamp'],
                    ]);

                    if ($this->translateStatus($record['application-status']) == 'referred') {
                        $payment->was_referred = true;
                    }

                    try {
                        $payment->save();
                    } catch (\Exception $e) {
                        Log::error('Failed to upsert Hometree finance record #' . $record['htf-quote-id'], [$e->getMessage()]);
                    }
                });
            });
        });
    }

    private function extractFirstNameFromString(string $inputString): string
    {
       return explode(' ', $inputString)[0];
    }

    private function extractMiddleNameFromString(string $inputString): string|null
    {
        $nameParts = explode(' ', $inputString);
        return count($nameParts) > 2 ? $nameParts[1] : null;
    }

    private function extractLastNameFromString(string $inputString): string
    {
        return last(explode(' ', $inputString));
    }

    private function integerOrNull(?string $inputString = null): ?int
    {
        return is_numeric($inputString) ? intval($inputString) : null;
    }

    private function translateStatus(string $status): string
    {
        return match ($status) {
            'pending-application' => 'new',
            'pending-customer-review' => 'pending',
            'pending-signature' => 'accepted',
            'pending-installation' => 'parked',
            'active' => 'active',
            'needs-manual-review' => 'referred',
            'pending-introduction-review' => 'referred',
            'final-abandoned' => 'cancelled',
            'final-review-failed' => 'declined',
            'final-declined' => 'declined',
            'final-cancelled' => 'cancelled',
            default => $status,
        };
    }
}
