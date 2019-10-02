<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Models to seed
    |--------------------------------------------------------------------------
    |
    | Define which models should be inverse seeded.
    | The 'default_models_to_seed' always go first than 'custom_models_to_seed'
    |
    */

    'default_models_to_seed' => [
        \TCG\Voyager\Models\Permission::class,
        \TCG\Voyager\Models\Role::class,
        "permission_role",
        \TCG\Voyager\Models\DataType::class,
        \TCG\Voyager\Models\DataRow::class,
        \TCG\Voyager\Models\Menu::class,
        \TCG\Voyager\Models\MenuItem::class,
        \TCG\Voyager\Models\Setting::class,
        \TCG\Voyager\Models\Translation::class,
    ],

    // Uncomment to also seed the users and their roles
    'custom_models_to_seed' => [
        // \App\User::class,
        // 'user_roles',
    ],

];
