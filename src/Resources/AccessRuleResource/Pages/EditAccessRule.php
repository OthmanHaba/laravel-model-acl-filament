<?php

declare(strict_types=1);

namespace OthmanHaba\LaravelModelAclFilament\Resources\AccessRuleResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use OthmanHaba\LaravelModelAclFilament\Resources\AccessRuleResource;

class EditAccessRule extends EditRecord
{
    protected static string $resource = AccessRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
