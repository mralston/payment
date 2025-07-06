<?php

return [
    /*
     * The Blade template to use as the root Inertia view.
     * If null, the package's default will be used.
     */
    'inertia_root_view' => env('FINANCE_INERTIA_ROOT_VIEW', null),

    // Tells the package how to resolve {parent} in routes with route model binding
    // The class used should implement \Mralston\Interfaces\FinanceParentModel
    'parent_model' => env('FINANCE_PARENT_MODEL', null),

    // A helper class, provided by the parent application, which the finance package can use to interface with the parent
    'helper' => env('FINANCE_HELPER', null),
];
