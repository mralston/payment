<x-mail::message>
# Cancellation

Please cancel the following application with immediate effect.

<x-mail::table>
|           |                                                               |
|-----------|---------------------------------------------------------------|
| Your ref  | {{ $payment->provider_foreign_id }}                           |
| Our ref   | {{ $payment->reference }}                                     |
| Customer  | {{ $payment->first_name }} {{ $payment->last_name }}          |
| Post Code | {{ $payment->addresses[0]['postCode'] ?? null }}              |
| Amount    | {{ Number::currency($payment->amount, 'GBP') }}               |
| Rate      | {{ $payment->apr }}%                                          |
| Term      | {{ $payment->term }}                                          |
| Upfront   | {{ Number::currency($payment->upfront_payment ?? 0, 'GBP') }} |
| Reason    | {{ $reason }}                                                 |
</x-mail::table>

If you have any queries, please contact our customer care team:

[customer.resolutions@projectsolaruk.com](mailto:customer.resolutions@projectsolaruk.com)

Kind regards,<br>
{{ config('app.name') }}
</x-mail::message>
