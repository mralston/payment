@component('mail::message')
Hello,

Please find sat note attached to this email for the following proposal.

@component('mail::table')
|                     |   |
|---------------------|---|
| Your Reference      | {{ $payment->provider_foreign_id }} |
| Our Reference       | {{ $payment->reference }} |
| Customer            | {{ $payment->first_name }} {{ $payment->last_name }} |
| Post Code           | {{ $payment->addresses->first()['post_code'] ?? null }} |
| Amount              | £{{ number_format($payment->loan_amount, 2) }} |
| Rate                | {{ $payment->apr }}% |
@endcomponent

If you have any queries, please contact our customer care team:

[customer.resolutions@projectsolaruk.com](mailto:customer.resolutions@projectsolaruk.com)

Kind Regards,<br>
{{ config('app.name') }}
@endcomponent
