<?php

declare(strict_types=1);

namespace OthmanHaba\LaravelModelAclFilament\Resources\AccessRuleResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use OthmanHaba\LaravelModelAclFilament\Resources\AccessRuleResource;

class ListAccessRules extends ListRecords
{
    protected static string $resource = AccessRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
