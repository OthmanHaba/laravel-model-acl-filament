# Laravel Model ACL — Filament UI

A [Filament](https://filamentphp.com) v4/v5 plugin that gives non-technical admins a
plain-language UI for [`othmanhaba/laravel-model-acl`](https://github.com/othmanhaba/laravel-model-acl).
Admins pick a **model**, an **action**, and a **condition** in a dropdown — no class
names, keys, or column names anywhere in the UI.

## Requirements

- PHP 8.2+
- Filament v4 or v5
- `othmanhaba/laravel-model-acl` ^0.1

## Installation

```bash
composer require othmanhaba/laravel-model-acl-filament
php artisan vendor:publish --tag=model-acl-filament-config
```

Register the plugin on your panel:

```php
use OthmanHaba\LaravelModelAclFilament\LaravelModelAclFilamentPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(LaravelModelAclFilamentPlugin::make());
}
```

## Configuration

Everything the UI knows about your models comes from `config/model-acl-filament.php`.
You describe each model once, in friendly terms, and the UI hides the rest.

```php
return [
    // Who a rule can be granted to => their display-name column.
    // Add your Spatie role model to grant rules to roles as well as users.
    'assignables' => [
        \App\Models\User::class => 'name',
        // \Spatie\Permission\Models\Role::class => 'name',
    ],

    // The models admins can write rules for.
    'managed_models' => [
        \App\Models\Ticket::class => [
            'label' => 'Tickets',                       // shown in the dropdown
            'title' => 'title',                         // column shown when picking a record
            'actions' => ['view', 'update', 'delete'],  // optional; defaults to view/create/update/delete
            'statuses' => ['open', 'pending', 'closed'],// enables the "by status" condition
            'status_column' => 'status',                // defaults to "status"
            'owner_column' => 'user_id',                // defaults to "user_id"
            'date_column' => 'created_at',              // defaults to "created_at"
            'columns' => [                              // enables the "custom filter" condition
                'priority' => 'Priority',
                'status' => 'Status',
            ],
        ],
    ],

    'navigation_group' => 'Access Control',
];
```

## What the admin sees

- **Access Rules** — a friendly builder: choose a **Model**, an **Action**
  (View / Update / …), whether to **grant or block**, and a **Condition** in plain
  English:
  - *Only records with a chosen status* — pick from the statuses you configured.
  - *Only records the user owns.*
  - *Only records within a date range.*
  - *Custom filter* — build one or more `column → condition → value` rows
    (e.g. `priority is greater than 3` **AND** `status is open`), joined with AND/OR.
    Only offered for models that declare `columns`.

  The rule's technical key, rule class, columns and priority are all derived from your
  config and the choices above. Blocks automatically outrank grants.
- **Assignments** — grant a rule to users (and roles, if configured) on each rule's page.
- **Access Tester** — pick a user, a record and an action, and see the live decision:
  Granted / Denied / No opinion.

## Pre-seeding example rules

Ships a seeder that creates one sensible starter rule per managed model
("view, owners only") so a fresh install has something to look at. Idempotent:

```bash
php artisan db:seed --class="OthmanHaba\LaravelModelAclFilament\Seeders\AccessRuleExampleSeeder"
```

## Local development

Until the parent package is on Packagist, point Composer at both packages with path
repositories in your app's `composer.json`:

```json
"repositories": [
    { "type": "path", "url": "../laravel-model-acl" },
    { "type": "path", "url": "../laravel-model-acl-filament" }
]
```

## License

MIT
