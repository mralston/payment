@component('mail::message')
@if(!empty($rep))
Dear {{ $rep->name }},
@else
Hello,
@endif

This payment has been accepted.

@component('mail::table')
|                     |   |
|---------------------|---|
| Reference           | {{ $payment->reference }} |
| Customer            | {{ $payment->first_name }} {{ $payment->last_name }} |
| Post Code           | {{ $payment->addresses->first()['postCode'] ?? null }} |
| Lender              | {{ $payment->paymentProvider->name }} |
| Amount              | {{ Number::currency($payment->amount, 'GBP') }} |
| Rate                | {{ $payment->apr ?? 0 }}% |
@endcomponent

@if($signingMethod == 'online')
The customer has been sent a link by e-mail to remotely sign the agreement.
@elseif($signingMethod == 'post')
The customer will receive documents through the post which they will be required to sign.
@elseif($signingMethod == 'online_non_interactive')
The customer will receive documents online through their registered e-mail / mobile which they will be required to sign.
@endif

Kind Regards,<br>
{{ config('app.name') }}
@endcomponent
