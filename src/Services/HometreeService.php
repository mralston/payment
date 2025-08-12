<?php

namespace Mralston\Payment\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentProvider;

class HometreeService
{
    public function handleWebhook(Collection $records)
    {
        $hometreeLender = PaymentProvider::firstWhere('identifier', 'hometree');

        Payment::withoutEvents(function() use ($records, $hometreeLender) {
            Payment::withoutTimestamps(function () use ($hometreeLender, $records) {
                $records->each(function($record) use ($hometreeLender) {
                    $payment = Payment::firstOrNew([
                        'payment_provider_id' => $hometreeLender->id,
                        'provider_foreign_id' => $record['htf_quote_id'],
                    ])->fill([
                        'uuid' => Str::uuid(),
                        'reference' => $record['psuk_quote_reference'],
                        'parentable_id' => $this->integerOrNull($record['psuk_quote_reference']),
                        'first_name' => $this->extractFirstNameFromString($record['customer_full_name']),
                        'middle_name' => $this->extractMiddleNameFromString($record['customer_full_name']),
                        'last_name' => $this->extractLastNameFromString($record['customer_full_name']),
                        'addresses.0.address1' => $record['customer_address_line_1'],
                        'addresses.0.address2' => $record['customer_address_line_2'],
                        'addresses.0.town' => $record['customer_address_line_3'],
                        'addresses.0.postcode' => $record['customer_postcode'],
                        'submitted_at' => $record['application_submitted_timestamp'],
                        'decision_received_at' => $record['application_complete_timestamp'],
                        'total_price' => !blank($record['quote_price']) ? Str::of($record['quote_price'])->replace([',', '£'], '')->toFloat() : null,
                        'payment_provider_id' => $hometreeLender->id,
                        'first_repayment' => !blank($record['upfront_payment_amount']) ? Str::of($record['upfront_payment_amount'])->replace([',', '£'], '')->toFloat() : null,
                        'monthly_repayment' => !blank($record['monthly_payment_amount']) ? Str::of($record['monthly_payment_amount'])->replace([',', '£'], '')->toFloat() : null,
                        'term' => !blank($record['account_term']) ? $record['account_term'] * 12 : null,
                        'total_payable' => !blank($record['total_payable']) ? Str::of($record['total_payable'])->replace([',', '£'], '')->toFloat() : null,
                        'payment_status_id' => $this->translateStatus($record['account_state']),
                        'created_at' => $record['quote_created_timestamp'],
                        'was_referred' => false,
                    ])->forceFill([
                        'created_at' => $record['quote_created_timestamp'],
                        'updated_at' => now(),
                    ]);

                    if ($this->translateStatus($record['account_state']) == 'referred') {
                        $payment->was_referred = true;
                    }

                    try {
                        $payment->save();
                    } catch (\Exception $e) {
                        Log::error('Failed to upsert Hometree finance record #' . $record['HTF Quote ID'], [$e->getMessage()]);
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

    private function integerOrNull(string $inputString): ?int
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
            'final-cancelled' => 'cancelled',
            default => $status,
        };
    }
}