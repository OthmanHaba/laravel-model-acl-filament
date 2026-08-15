<?php

declare(strict_types=1);

namespace OthmanHaba\LaravelModelAclFilament\Support;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Gate for the package's admin screens. Restricts them to the user ids in
 * `model-acl-filament.admin_ids`; allows everyone when that is null/empty.
 */
class Access
{
    public static function allows(?Authenticatable $user): bool
    {
        $ids = config('model-acl-filament.admin_ids');

        if (empty($ids)) {
            return true;
        }

        return $user !== null && in_array($user->getAuthIdentifier(), (array) $ids);
    }
}
