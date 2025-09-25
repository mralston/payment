<x-mail::message>
@if(!empty($customer))
Dear {{ $payment->first_name }} {{ $payment->last_name }},
@else
Hello,
@endif

Your finance application has been accepted by {{ $payment->paymentProvider->name }}.

@if($signingMethod == 'online')
@if($payment->was_referred)
Please use the button below to sign your loan agreement.

If our representative, {{ $rep->name }}, is still with you, they will guide you through the rest of the process.
@else
If our representative, {{ $rep->name }}, is still with you, they will guide you through the rest of the process.

Please use the button below to sign your loan agreement.
@endif

@component('mail::button', ['url' => route('payment.remote-sign', $payment->uuid)])
Sign Agreement
@endcomponent
@elseif($signingMethod == 'post')
You will receive finance documents through the post which you will need to sign.
@elseif($signingMethod == 'online_non_interactive')
You will receive a separate e-mail from {{ $payment->paymentProvider->name }} detailing how to complete the application process.
@endif

Kind Regards,<br>
{{ config('app.name') }}
</x-mail::message>
