<?php

declare(strict_types=1);

namespace OthmanHaba\LaravelModelAclFilament\Resources;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use OthmanHaba\LaravelModelAcl\Rules\DateRangeRule;
use OthmanHaba\LaravelModelAcl\Rules\StatusRule;
use OthmanHaba\LaravelModelAclFilament\Resources\AccessRuleResource\Pages;
use OthmanHaba\LaravelModelAclFilament\Resources\AccessRuleResource\RelationManagers\AssignmentsRelationManager;
use OthmanHaba\LaravelModelAclFilament\Support\ManagedModels;

class AccessRuleResource extends Resource
{
    protected static ?string $model = \OthmanHaba\LaravelModelAcl\Models\AccessRule::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    public static function getNavigationGroup(): ?string
    {
        return config('model-acl-filament.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return 'access rule';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('What can they access?')
                ->description('Pick the thing to protect and whether this rule grants or blocks access.')
                ->columns(2)
                ->schema([
                    Select::make('ruleable_type')
                        ->label('Model')
                        ->options(ManagedModels::modelOptions())
                        ->required()
                        ->live()
                        ->native(false),

                    Select::make('action')
                        ->label('Action')
                        ->options(fn (Get $get) => ManagedModels::actionOptions($get('ruleable_type')))
                        ->required()
                        ->native(false)
                        ->default('view'),

                    Toggle::make('is_deny_rule')
                        ->label('Block access')
                        ->helperText('Leave off to grant access. Turn on to block it — a block always wins over a grant.')
                        ->columnSpanFull(),
                ]),

            Section::make('Under what condition?')
                ->description('Choose which records this rule covers.')
                ->columns(2)
                ->schema([
                    Select::make('rule_class')
                        ->label('Condition')
                        ->options(fn (Get $get) => ManagedModels::conditionOptions($get('ruleable_type')))
                        ->required()
                        ->live()
                        ->native(false)
                        ->columnSpanFull(),

                    Select::make('settings.statuses')
                        ->label('Allowed statuses')
                        ->multiple()
                        ->options(fn (Get $get) => ManagedModels::statusOptions($get('ruleable_type')))
                        ->required()
                        ->visible(fn (Get $get) => $get('rule_class') === StatusRule::class)
                        ->columnSpanFull(),

                    DatePicker::make('settings.from')
                        ->label('From')
                        ->native(false)
                        ->visible(fn (Get $get) => $get('rule_class') === DateRangeRule::class),
                    DatePicker::make('settings.to')
                        ->label('To')
                        ->native(false)
                        ->visible(fn (Get $get) => $get('rule_class') === DateRangeRule::class),
                ]),

            Section::make('Details')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Label')
                        ->helperText('Optional. Left blank, we name it for you.')
                        ->maxLength(255),

                    Toggle::make('active')
                        ->label('Active')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Label')->searchable()->sortable(),
                TextColumn::make('ruleable_type')
                    ->label('Model')
                    ->formatStateUsing(fn (?string $state) => ManagedModels::modelLabel($state))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('action')
                    ->label('Action')
                    ->state(fn ($record) => \Illuminate\Support\Str::headline(
                        ManagedModels::actionFromKey($record->key) ?? ''
                    )),
                TextColumn::make('rule_class')
                    ->label('Condition')
                    ->formatStateUsing(fn (string $state) => ManagedModels::conditionLabel($state))
                    ->wrap(),
                IconColumn::make('is_deny_rule')
                    ->label('Blocks')
                    ->boolean()
                    ->trueIcon('heroicon-o-no-symbol')
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-check-circle')
                    ->falseColor('success'),
                IconColumn::make('active')->boolean(),
                TextColumn::make('assignments_count')
                    ->counts('assignments')
                    ->label('Granted to')
                    ->badge(),
            ])
            ->defaultSort('priority', 'desc');
    }

    /**
     * Turn the friendly form state into the columns the ACL engine stores:
     * computes the rule key, fills column settings from config, auto-names the
     * rule, and prioritises blocks over grants. Used by both create and edit.
     */
    public static function prepare(array $data): array
    {
        $model = $data['ruleable_type'] ?? null;
        $action = $data['action'] ?? 'view';

        if ($model) {
            $data['key'] = ManagedModels::buildKey($model, $action);
        }

        $data['settings'] = array_merge(
            $data['settings'] ?? [],
            ManagedModels::columnSettings($model, $data['rule_class'] ?? null),
        );

        if (empty($data['name'])) {
            $data['name'] = \Illuminate\Support\Str::headline($action) . ' ' . ManagedModels::modelLabel($model);
        }

        // Blocks must outrank grants under every resolution strategy.
        $data['priority'] = ! empty($data['is_deny_rule']) ? 100 : 0;

        unset($data['action']);

        return $data;
    }

    /** Restore the friendly `action` field from the stored key when editing. */
    public static function hydrate(array $data): array
    {
        $data['action'] = ManagedModels::actionFromKey($data['key'] ?? null);

        return $data;
    }

    public static function getRelations(): array
    {
        return [
            AssignmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccessRules::route('/'),
            'create' => Pages\CreateAccessRule::route('/create'),
            'edit' => Pages\EditAccessRule::route('/{record}/edit'),
        ];
    }
}
