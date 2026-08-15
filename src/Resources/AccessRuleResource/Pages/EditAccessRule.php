<?php

declare(strict_types=1);

namespace OthmanHaba\LaravelModelAclFilament\Resources\AccessRuleResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use OthmanHaba\LaravelModelAclFilament\Resources\AccessRuleResource;

class EditAccessRule extends EditRecord
{
    protected static string $resource = AccessRuleResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return AccessRuleResource::hydrate($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return AccessRuleResource::prepare($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
