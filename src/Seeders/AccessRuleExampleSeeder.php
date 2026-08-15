<?php

declare(strict_types=1);

namespace OthmanHaba\LaravelModelAclFilament\Seeders;

use Illuminate\Database\Seeder;
use OthmanHaba\LaravelModelAcl\Models\AccessRule;
use OthmanHaba\LaravelModelAcl\Rules\OwnershipRule;
use OthmanHaba\LaravelModelAclFilament\Support\ManagedModels;

/**
 * Seeds one sensible starter rule per managed model — "view, owners only" —
 * so a fresh install has example rules to look at and edit. Idempotent.
 *
 *   php artisan db:seed --class="OthmanHaba\LaravelModelAclFilament\Seeders\AccessRuleExampleSeeder"
 */
class AccessRuleExampleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (array_keys(ManagedModels::all()) as $class) {
            $key = ManagedModels::buildKey($class, 'view');

            AccessRule::firstOrCreate(
                ['key' => $key, 'rule_class' => OwnershipRule::class],
                [
                    'name' => 'View ' . ManagedModels::modelLabel($class) . ' — owners only',
                    'settings' => ManagedModels::columnSettings($class, OwnershipRule::class),
                    'ruleable_type' => $class,
                    'priority' => 0,
                    'is_deny_rule' => false,
                    'active' => true,
                ],
            );
        }
    }
}
