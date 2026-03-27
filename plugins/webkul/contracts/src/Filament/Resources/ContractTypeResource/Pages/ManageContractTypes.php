<?php

namespace Webkul\Contracts\Filament\Resources\ContractTypeResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Webkul\Contracts\Filament\Resources\ContractTypeResource;

class ManageContractTypes extends ManageRecords
{
    protected static string $resource = ContractTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon('heroicon-o-plus-circle'),
        ];
    }
}
