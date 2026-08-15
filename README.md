# Laravel Model ACL — Filament UI

A [Filament](https://filamentphp.com) v4/v5 plugin that gives you a full admin UI for
[`othmanhaba/laravel-model-acl`](https://github.com/othmanhaba/laravel-model-acl):
create and edit access rules, assign them to users and roles, and test decisions live.

## Requirements

- PHP 8.2+
- Filament v4 or v5
- `othmanhaba/laravel-model-acl` ^0.1

## Installation

```bash
composer require othmanhaba/laravel-model-acl-filament
```

Publish the config if you want to customise it:

```bash
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

`config/model-acl-filament.php`:

```php
return [
    // Models a rule can be assigned to => their display-title column.
    // Add your Spatie role model here to assign rules to roles too.
    'assignables' => [
        \App\Models\User::class => 'name',
        // \Spatie\Permission\Models\Role::class => 'name',
    ],

    // Models selectable in the Access Tester => their display-title column.
    'testable_models' => [
        // \App\Models\Ticket::class => 'title',
    ],

    'navigation_group' => 'Access Control',
];
```

## What you get

- **Access Rules resource** — CRUD for rules. The settings form adapts to the selected
  rule type (Status, Date range, Ownership, Attribute), showing only that rule's fields.
- **Assignments** — a relation manager on each rule to attach/detach users (and roles,
  if configured). This is where rules are granted to people.
- **Spatie roles** — add your role model to `assignables` and assign rules to roles
  exactly like users; the ACL package already resolves rules through a user's roles.
- **Access Tester** — pick a user, a record and an action, and see the live decision:
  Granted / Denied / No opinion (no applicable rules).

## Notes

- A rule's **key** must start with the action, e.g. `view_ticket` — the ACL package
  matches rules by the action prefix before the first underscore.
- The **Applies to model** field is the fully-qualified model class the rule targets;
  leave it empty for a global rule.
- The first entry in `assignables` is used as the "acting user" source in the Access
  Tester, so keep your user model first.

## Local development

Until the parent package is published on Packagist, point Composer at it with a path
repository in your app's `composer.json`:

```json
"repositories": [
    { "type": "path", "url": "../laravel-model-acl" }
]
```

## License

MIT
