<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Finance') }}</title>
        @vite(['resources/js/app.js'], 'vendor/mralston/finance/build')
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
