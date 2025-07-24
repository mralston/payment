<?php

return [
    /*
     * The Blade template to use as the root Inertia view.
     * If null, the package's default will be used.
     */
    'inertia_root_view' => env('PAYMENT_INERTIA_ROOT_VIEW', null),

    // Tells the package how to resolve {parent} in routes with route model binding
    // The class used should implement \Mralston\Payment\Interfaces\PaymentParentModel
    'parent_model' => env('PAYMENT_PARENT_MODEL', null),

    // The parent application's user model
    'user_model' => env('PAYMENT_USER_MODEL', 'App\Models\User'),

    // A helper class, provided by the parent application, which the Payment package can use to interface with the parent
    'helper' => env('PAYMENT_HELPER', null),

    /*
    |--------------------------------------------------------------------------
    | Tandem Finance
    |--------------------------------------------------------------------------
    |
    | The API key and endpoint to be used when talking to the Tandem Finance API
    |
    */

    'tandem' => [
        'api_key' => env('TANDEM_API_KEY'),
        'endpoint' => env('TANDEM_ENDPOINT', config('app.env')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Propensio Finance
    |--------------------------------------------------------------------------
    |
    | The IBC reference and endpoint to be used when talking to the Propensio Finance API
    |
    */

    'propensio' => [
        'ibc_ref' => env('PROPENSIO_IBC_REF') ?? '',
        'endpoint' => env('PROPENSIO_ENDPOINT', config('app.env')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Hometree Finance
    |--------------------------------------------------------------------------
    |
    | The API key and endpoint to be used when talking to the Allium Money API
    |
    */

    'hometree' => [
        'api_key' => env('HOMETREE_API_KEY'),
        'endpoint' => env('HOMETREE_ENDPOINT', config('app.env')),
    ],

];
