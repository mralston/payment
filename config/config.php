<?php

return [
    /*
     * The Blade template to use as the root Inertia view.
     * If null, the package's default will be used.
     */
    'inertia_root_view' => env('FINANCE_INERTIA_ROOT_VIEW', null),

    /*
     * Any data to pass to the parent application's Inertia root view.
     * This will be merged with the data already passed to Inertia::render().
     */
    'inertia_root_view_data' => [],

    // Tells the package how to resolve {parent} in routes with route model binding
    // The class used should implement \Mralston\Interfaces\FinanceParentModel
    'parent_model' => env('FINANCE_PARENT_MODEL', null),
];
