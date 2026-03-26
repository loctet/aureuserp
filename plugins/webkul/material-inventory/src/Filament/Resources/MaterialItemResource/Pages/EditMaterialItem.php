<?php

namespace Webkul\MaterialInventory\Filament\Resources\MaterialItemResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource;

class EditMaterialItem extends EditRecord
{
    protected static string $resource = MaterialItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
