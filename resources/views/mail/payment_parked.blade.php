@component('mail::message')
@if(!empty($rep))
Dear {{ $rep->name }},
@else
Hello,
@endif

This payment has been executed.

@component('mail::table')
|                     |   |
|---------------------|---|
| Reference           | {{ $payment->reference }} |
| Customer            | {{ $payment->first_name }} {{ $payment->last_name }} |
| Post Code           | {{ $payment->addresses->first()['postCode'] ?? null }} |
| Lender              | {{ $payment->paymentProvider->name }} |
| Amount              | {{ Number::currency($payment->amount, 'GBP') }} |
| Rate                | {{ $payment->apr }}% |
@endcomponent

@component('mail::button', ['url' => route('payments.show', $payment)])
View Payment
@endcomponent

Kind Regards,<br>
{{ config('app.name') }}
@endcomponent
