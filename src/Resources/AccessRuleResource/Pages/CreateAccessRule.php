<?php

declare(strict_types=1);

namespace OthmanHaba\LaravelModelAclFilament\Resources\AccessRuleResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use OthmanHaba\LaravelModelAclFilament\Resources\AccessRuleResource;

class CreateAccessRule extends CreateRecord
{
    protected static string $resource = AccessRuleResource::class;
}
