<?php

declare(strict_types=1);

namespace OthmanHaba\LaravelModelAclFilament\Support;

use Illuminate\Support\Str;
use OthmanHaba\LaravelModelAcl\Rules\DateRangeRule;
use OthmanHaba\LaravelModelAcl\Rules\OwnershipRule;
use OthmanHaba\LaravelModelAcl\Rules\StatusRule;

/**
 * Translates the friendly `managed_models` config into the options and
 * plain-language labels the UI shows, and back into the raw columns/keys the
 * ACL engine stores. Keeps class names, keys and column names out of the UI.
 */
class ManagedModels
{
    public const DEFAULT_ACTIONS = ['view', 'create', 'update', 'delete'];

    /** Plain-language name for each rule type. */
    public const CONDITIONS = [
        StatusRule::class => 'Only records with a chosen status',
        OwnershipRule::class => 'Only records the user owns',
        DateRangeRule::class => 'Only records within a date range',
    ];

    /** @return array<class-string, array<string, mixed>> */
    public static function all(): array
    {
        return config('model-acl-filament.managed_models', []);
    }

    /** @return array<string, mixed> */
    public static function config(?string $class): array
    {
        return $class ? (static::all()[$class] ?? []) : [];
    }

    /** Model dropdown: class => friendly label. */
    public static function modelOptions(): array
    {
        return collect(static::all())
            ->mapWithKeys(fn (array $cfg, string $class) => [
                $class => $cfg['label'] ?? class_basename($class),
            ])
            ->all();
    }

    public static function modelLabel(?string $class): string
    {
        return static::config($class)['label'] ?? ($class ? class_basename($class) : 'All models');
    }

    /** Action dropdown for a model: action => "View" etc. */
    public static function actionOptions(?string $class): array
    {
        $actions = static::config($class)['actions'] ?? static::DEFAULT_ACTIONS;

        return collect($actions)
            ->mapWithKeys(fn (string $a) => [$a => Str::headline($a)])
            ->all();
    }

    /** Condition dropdown, hiding "by status" when the model has no statuses. */
    public static function conditionOptions(?string $class): array
    {
        $options = static::CONDITIONS;

        if (empty(static::config($class)['statuses'])) {
            unset($options[StatusRule::class]);
        }

        return $options;
    }

    public static function conditionLabel(string $ruleClass): string
    {
        return static::CONDITIONS[$ruleClass] ?? class_basename($ruleClass);
    }

    /** Status multi-select options: status => "Open". */
    public static function statusOptions(?string $class): array
    {
        return collect(static::config($class)['statuses'] ?? [])
            ->mapWithKeys(fn (string $s) => [$s => Str::headline($s)])
            ->all();
    }

    public static function titleColumn(?string $class): string
    {
        return static::config($class)['title'] ?? 'id';
    }

    /** Rule key stored by the engine, e.g. "view_ticket". */
    public static function buildKey(string $class, string $action): string
    {
        return $action . '_' . Str::snake(class_basename($class));
    }

    /** Reverse of buildKey: the action is always the first token. */
    public static function actionFromKey(?string $key): ?string
    {
        return $key ? Str::before($key, '_') : null;
    }

    /**
     * Column settings the engine needs for a rule type, pulled from config so
     * the admin never types a column name.
     */
    public static function columnSettings(?string $class, ?string $ruleClass): array
    {
        $cfg = static::config($class);

        return match ($ruleClass) {
            StatusRule::class => ['status_column' => $cfg['status_column'] ?? 'status'],
            OwnershipRule::class => [
                'owner_column' => $cfg['owner_column'] ?? 'user_id',
                'user_id_column' => $cfg['user_id_column'] ?? 'id',
            ],
            DateRangeRule::class => ['date_column' => $cfg['date_column'] ?? 'created_at'],
            default => [],
        };
    }
}
