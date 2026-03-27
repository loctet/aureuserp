<?php

namespace Webkul\Contracts\Filament\Resources\ContractResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Webkul\Contracts\Filament\Resources\ContractResource;

class ManageContracts extends ManageRecords
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon('heroicon-o-plus-circle'),
        ];
    }
}
