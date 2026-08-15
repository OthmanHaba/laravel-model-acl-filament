<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Assignable Models
    |--------------------------------------------------------------------------
    |
    | Models that an access rule can be assigned to, mapped to the column used
    | as their display title in the assignment UI. Add your Spatie role model
    | here to assign rules to roles as well as users.
    |
    */
    'assignables' => [
        \App\Models\User::class => 'name',
        // \Spatie\Permission\Models\Role::class => 'name',
    ],

    /*
    |--------------------------------------------------------------------------
    | Testable Models
    |--------------------------------------------------------------------------
    |
    | Models selectable in the Access Tester page, mapped to the column used
    | as their display title when picking a record.
    |
    */
    'testable_models' => [
        // \App\Models\Ticket::class => 'title',
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    */
    'navigation_group' => 'Access Control',
];
