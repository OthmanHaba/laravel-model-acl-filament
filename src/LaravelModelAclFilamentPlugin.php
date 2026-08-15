<?php

declare(strict_types=1);

namespace OthmanHaba\LaravelModelAclFilament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use OthmanHaba\LaravelModelAclFilament\Pages\AccessTester;
use OthmanHaba\LaravelModelAclFilament\Resources\AccessRuleResource;

class LaravelModelAclFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'laravel-model-acl';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                AccessRuleResource::class,
            ])
            ->pages([
                AccessTester::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
