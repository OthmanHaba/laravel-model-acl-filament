<?php

declare(strict_types=1);

namespace OthmanHaba\LaravelModelAclFilament\Resources;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use OthmanHaba\LaravelModelAcl\Rules\AttributeRule;
use OthmanHaba\LaravelModelAcl\Rules\DateRangeRule;
use OthmanHaba\LaravelModelAcl\Rules\OwnershipRule;
use OthmanHaba\LaravelModelAcl\Rules\StatusRule;
use OthmanHaba\LaravelModelAclFilament\Resources\AccessRuleResource\Pages;
use OthmanHaba\LaravelModelAclFilament\Resources\AccessRuleResource\RelationManagers\AssignmentsRelationManager;

class AccessRuleResource extends Resource
{
    protected static ?string $model = \OthmanHaba\LaravelModelAcl\Models\AccessRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $modelLabel = 'Access Rule';

    public static function getNavigationGroup(): ?string
    {
        return config('model-acl-filament.navigation_group');
    }

    /**
     * Options for the rule_class select, derived from the parent package's
     * configured built-in rules (slug => class).
     */
    protected static function ruleClassOptions(): array
    {
        return collect(config('access-control.built_in_rules', []))
            ->mapWithKeys(fn (string $class, string $slug) => [$class => Str::headline($slug)])
            ->all();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            TextInput::make('key')
                ->required()
                ->helperText('Must start with the action, e.g. "view_ticket". Rules are matched by the action prefix before the first underscore.'),

            Select::make('rule_class')
                ->label('Rule type')
                ->options(static::ruleClassOptions())
                ->required()
                ->live(),

            TextInput::make('ruleable_type')
                ->label('Applies to model (FQCN)')
                ->helperText('Fully-qualified model class this rule applies to. Leave empty for a global rule.'),

            TextInput::make('priority')
                ->numeric()
                ->default(0)
                ->helperText('Higher priority rules are evaluated first.'),

            Toggle::make('is_deny_rule')
                ->label('Deny rule')
                ->helperText('A deny rule overrides any matching allow rule.'),

            Toggle::make('active')
                ->default(true),

            // --- Settings: shown per selected rule type -----------------------

            TagsInput::make('settings.statuses')
                ->label('Allowed statuses')
                ->visible(fn (Get $get) => $get('rule_class') === StatusRule::class),
            TextInput::make('settings.status_column')
                ->label('Status column')
                ->placeholder('status')
                ->visible(fn (Get $get) => $get('rule_class') === StatusRule::class),

            DatePicker::make('settings.from')
                ->visible(fn (Get $get) => $get('rule_class') === DateRangeRule::class),
            DatePicker::make('settings.to')
                ->visible(fn (Get $get) => $get('rule_class') === DateRangeRule::class),
            TextInput::make('settings.date_column')
                ->label('Date column')
                ->placeholder('created_at')
                ->visible(fn (Get $get) => $get('rule_class') === DateRangeRule::class),

            TextInput::make('settings.owner_column')
                ->label('Owner column (on the model)')
                ->placeholder('user_id')
                ->visible(fn (Get $get) => $get('rule_class') === OwnershipRule::class),
            TextInput::make('settings.user_id_column')
                ->label('User identifier column')
                ->placeholder('id')
                ->visible(fn (Get $get) => $get('rule_class') === OwnershipRule::class),

            TextInput::make('settings.model_attribute')
                ->label('Model attribute')
                ->visible(fn (Get $get) => $get('rule_class') === AttributeRule::class),
            TextInput::make('settings.user_attribute')
                ->label('User attribute (compared against the model attribute)')
                ->visible(fn (Get $get) => $get('rule_class') === AttributeRule::class),
            TextInput::make('settings.static_value')
                ->label('Static value (used when no user attribute is set)')
                ->visible(fn (Get $get) => $get('rule_class') === AttributeRule::class),
            Select::make('settings.operator')
                ->options(array_combine(
                    ['=', '!=', '>', '>=', '<', '<=', 'in', 'not_in'],
                    ['=', '!=', '>', '>=', '<', '<=', 'in', 'not_in'],
                ))
                ->default('=')
                ->visible(fn (Get $get) => $get('rule_class') === AttributeRule::class),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('key')->searchable()->sortable(),
                TextColumn::make('rule_class')
                    ->label('Rule type')
                    ->formatStateUsing(fn (string $state) => class_basename($state)),
                TextColumn::make('ruleable_type')
                    ->label('Model')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : 'Global')
                    ->badge(),
                TextColumn::make('priority')->sortable(),
                IconColumn::make('is_deny_rule')->label('Deny')->boolean(),
                IconColumn::make('active')->boolean(),
                TextColumn::make('assignments_count')
                    ->counts('assignments')
                    ->label('Assignees'),
            ])
            ->defaultSort('priority', 'desc');
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
