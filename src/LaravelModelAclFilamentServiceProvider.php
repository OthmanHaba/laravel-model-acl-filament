<?php

declare(strict_types=1);

namespace OthmanHaba\LaravelModelAclFilament;

use Illuminate\Support\ServiceProvider;

class LaravelModelAclFilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/model-acl-filament.php',
            'model-acl-filament'
        );
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'model-acl-filament');

        $this->publishes([
            __DIR__ . '/../config/model-acl-filament.php' => config_path('model-acl-filament.php'),
        ], 'model-acl-filament-config');
    }
}
