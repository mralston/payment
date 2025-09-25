@component('mail::message')
Hello,

This payment has been cancelled.

@component('mail::table')
|                     |   |
|---------------------|---|
| Reference           | [{{ $payment->reference }}]({{ route('payments.show', $payment) }}) |
| {{ $parentModelDescription }} ID | [{{ $payment->parentable_id }}]({{ route($parentRouteName, $parent) }}) |
@if(!empty($payment->parent->iq_id))
| IQ Ref              | {{ $payment->parentable->iq_id }} |
@endif
| Customer            | {{ $payment->first_name }} {{ $payment->last_name }} |
| Post Code           | {{ $payment->addresses->first()['post_code'] ?? null }} |
| Lender              | {{ $payment->paymentProvider->name }} |
| Amount              | {{ Number::currency($payment->amount, 'GBP') }} |
| Rate                | {{ $payment->apr }}% |
| Reason              | {{ $cancellation->reason }} |
| Source              | {{ Str::title($cancellation->source) }} |
| Actor               | {{ $cancellation->user->name ?? null }} |
@endcomponent

@component('mail::button', ['url' => route('payments.show', $payment)])
View Payment
@endcomponent

Kind Regards,<br>
{{ config('app.name') }}
@endcomponent
