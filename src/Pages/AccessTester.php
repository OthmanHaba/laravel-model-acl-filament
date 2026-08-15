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
use OthmanHaba\LaravelModelAclFilament\Support\Access;
use OthmanHaba\LaravelModelAclFilament\Support\ManagedModels;

class AccessTester extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-beaker';

    protected string $view = 'model-acl-filament::pages.access-tester';

    public ?array $data = [];

    /** @var bool|null Result of the last test: true=grant, false=deny, null=no opinion. */
    public ?bool $decision = null;

    public bool $tested = false;

    public static function getNavigationGroup(): ?string
    {
        return config('model-acl-filament.navigation_group');
    }

    public static function canAccess(): bool
    {
        return Access::allows(auth()->user());
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
                    ->options(ManagedModels::modelOptions())
                    ->required()
                    ->live()
                    ->native(false),

                Select::make('record_id')
                    ->label('Record')
                    ->required()
                    ->searchable()
                    ->options(function (Get $get): array {
                        $class = $get('model_class');

                        if (! $class || ! isset(ManagedModels::all()[$class])) {
                            return [];
                        }

                        return $class::query()
                            ->pluck(ManagedModels::titleColumn($class), (new $class)->getKeyName())
                            ->all();
                    }),

                Select::make('action')
                    ->label('Action')
                    ->options(fn (Get $get) => ManagedModels::actionOptions($get('model_class')))
                    ->required()
                    ->native(false),
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
