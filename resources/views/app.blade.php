<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Payment') }}</title>
        @vite(['resources/js/app.js'], 'vendor/mralston/payment/build')
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
