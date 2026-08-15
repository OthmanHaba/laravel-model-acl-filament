<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Assignable Models
    |--------------------------------------------------------------------------
    |
    | Who a rule can be granted to, mapped to the column used as their display
    | name. Add your Spatie role model here to grant rules to roles too.
    |
    */
    'assignables' => [
        \App\Models\User::class => 'name',
        // \Spatie\Permission\Models\Role::class => 'name',
    ],

    /*
    |--------------------------------------------------------------------------
    | Managed Models
    |--------------------------------------------------------------------------
    |
    | The models an admin can write rules for, described in plain terms so the
    | rule builder never exposes class names or column names. Each entry:
    |
    |   'label'         Friendly name shown in the dropdown (e.g. "Tickets").
    |   'title'         Column used when picking an individual record.
    |   'actions'       Actions offered for this model (defaults to the four below).
    |   'statuses'      Selectable statuses — enables the "by status" condition.
    |   'status_column' Column the status lives in (default: status).
    |   'owner_column'  Column holding the owner's id (default: user_id).
    |   'date_column'   Column used by the "by date range" condition (default: created_at).
    |
    */
    'managed_models' => [
        // \App\Models\Ticket::class => [
        //     'label' => 'Tickets',
        //     'title' => 'title',
        //     'actions' => ['view', 'create', 'update', 'delete'],
        //     'statuses' => ['open', 'pending', 'closed'],
        //     'status_column' => 'status',
        //     'owner_column' => 'user_id',
        //     'date_column' => 'created_at',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    */
    'navigation_group' => 'Access Control',
];
