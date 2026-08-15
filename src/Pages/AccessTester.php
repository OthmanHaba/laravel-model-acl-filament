<?php

declare(strict_types=1);

namespace OthmanHaba\LaravelModelAclFilament\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use OthmanHaba\LaravelModelAcl\Services\AccessControlService;

class AccessTester extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static string $view = 'model-acl-filament::pages.access-tester';

    protected static ?string $title = 'Access Tester';

    public ?array $data = [];

    /** @var bool|null Result of the last test: true=grant, false=deny, null=no opinion. */
    public ?bool $decision = null;

    public bool $tested = false;

    public static function getNavigationGroup(): ?string
    {
        return config('model-acl-filament.navigation_group');
    }

    /** @return array<class-string, string> */
    protected static function testableModels(): array
    {
        return config('model-acl-filament.testable_models', []);
    }

    protected static function userModel(): string
    {
        return array_key_first(config('model-acl-filament.assignables', [])) ?? \App\Models\User::class;
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        $userModel = static::userModel();
        $userTitle = config('model-acl-filament.assignables.' . $userModel, 'name');

        return $schema
            ->components([
                Select::make('user_id')
                    ->label('User')
                    ->options($userModel::query()->pluck($userTitle, (new $userModel)->getKeyName()))
                    ->searchable()
                    ->required(),

                Select::make('model_class')
                    ->label('Model')
                    ->options(collect(static::testableModels())
                        ->keys()
                        ->mapWithKeys(fn (string $c) => [$c => class_basename($c)])
                        ->all())
                    ->required()
                    ->live(),

                Select::make('record_id')
                    ->label('Record')
                    ->required()
                    ->searchable()
                    ->options(function (Get $get): array {
                        $class = $get('model_class');
                        $models = static::testableModels();

                        if (! $class || ! isset($models[$class])) {
                            return [];
                        }

                        return $class::query()->pluck($models[$class], (new $class)->getKeyName())->all();
                    }),

                Select::make('action')
                    ->options(collect(config('access-control.standard_actions', []))
                        ->mapWithKeys(fn (string $a) => [$a => $a])
                        ->all())
                    ->required(),
            ])
            ->statePath('data');
    }

    public function runTest(): void
    {
        $data = $this->form->getState();

        $userModel = static::userModel();
        $user = $userModel::findOrFail($data['user_id']);
        $record = $data['model_class']::findOrFail($data['record_id']);

        $this->decision = app(AccessControlService::class)->decide($user, $data['action'], $record);
        $this->tested = true;
    }
}
