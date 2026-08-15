<?php

declare(strict_types=1);

namespace OthmanHaba\LaravelModelAclFilament\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use OthmanHaba\LaravelModelAcl\Services\AccessControlService;
use OthmanHaba\LaravelModelAclFilament\Support\Access;
use OthmanHaba\LaravelModelAclFilament\Support\ManagedModels;

/**
 * Shows, for one user and one model, every record with a Granted / Denied /
 * No-rule verdict — the whole "what can they see?" picture at a glance.
 */
class UserAccess extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-eye';

    protected string $view = 'model-acl-filament::pages.user-access';

    /** Cap the scan so a huge table never melts the page. */
    protected const LIMIT = 200;

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return 'User Access';
    }

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
        $this->form->fill(['action' => 'view']);
    }

    public function form(Schema $schema): Schema
    {
        $userModel = static::userModel();
        $userTitle = config('model-acl-filament.assignables.' . $userModel, 'name');

        return $schema
            ->columns(3)
            ->components([
                Select::make('user_id')
                    ->label('User')
                    ->options($userModel::query()->pluck($userTitle, (new $userModel)->getKeyName()))
                    ->searchable()
                    ->required()
                    ->live()
                    ->native(false),

                Select::make('model_class')
                    ->label('Model')
                    ->options(ManagedModels::modelOptions())
                    ->required()
                    ->live()
                    ->native(false),

                Select::make('action')
                    ->label('Action')
                    ->options(fn (Get $get) => ManagedModels::actionOptions($get('model_class')))
                    ->required()
                    ->live()
                    ->native(false),
            ])
            ->statePath('data');
    }

    /**
     * @return array<int, array{title: string, decision: bool|null}>
     */
    public function getRowsProperty(): array
    {
        $data = $this->data;

        if (empty($data['user_id']) || empty($data['model_class']) || empty($data['action'])) {
            return [];
        }

        $user = static::userModel()::find($data['user_id']);
        $class = $data['model_class'];

        if (! $user || ! isset(ManagedModels::all()[$class])) {
            return [];
        }

        $title = ManagedModels::titleColumn($class);
        $service = app(AccessControlService::class);

        return $class::query()
            ->limit(static::LIMIT)
            ->get()
            ->map(fn ($record) => [
                'title' => (string) (data_get($record, $title) ?? ('#' . $record->getKey())),
                'decision' => $service->decide($user, $data['action'], $record),
            ])
            ->all();
    }

    public function getSummaryProperty(): array
    {
        $rows = $this->rows;

        return [
            'granted' => collect($rows)->where('decision', true)->count(),
            'denied' => collect($rows)->where('decision', false)->count(),
            'noRule' => collect($rows)->whereNull('decision')->count(),
            'label' => Str::headline($this->data['action'] ?? 'view'),
        ];
    }
}
