<?php

declare(strict_types=1);

namespace OthmanHaba\LaravelModelAclFilament\Resources\AccessRuleResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    /**
     * Configured assignable models, mapped to their display-title column.
     *
     * @return array<class-string, string>
     */
    protected static function assignables(): array
    {
        return config('model-acl-filament.assignables', []);
    }

    /** Type select options: class => short label. */
    protected static function typeOptions(): array
    {
        return collect(static::assignables())
            ->keys()
            ->mapWithKeys(fn (string $class) => [$class => class_basename($class)])
            ->all();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('assignable_type')
                ->label('Assign to')
                ->options(static::typeOptions())
                ->required()
                ->live(),

            Select::make('assignable_id')
                ->label('Record')
                ->required()
                ->searchable()
                ->options(function (Get $get): array {
                    $type = $get('assignable_type');
                    $assignables = static::assignables();

                    if (! $type || ! isset($assignables[$type])) {
                        return [];
                    }

                    return $type::query()->pluck($assignables[$type], (new $type)->getKeyName())->all();
                }),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('assignable_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state) => class_basename($state))
                    ->badge(),
                TextColumn::make('assignable_id')
                    ->label('Record')
                    ->formatStateUsing(function ($state, Model $record): string {
                        $type = $record->assignable_type;
                        $column = static::assignables()[$type] ?? null;
                        $model = $column ? $type::find($state) : null;

                        return $model?->{$column} ?? (string) $state;
                    }),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                DeleteAction::make(),
            ]);
    }
}
