<?php

namespace Webkul\Contracts\Filament\Resources\AllocationResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Webkul\Contracts\Filament\Resources\AllocationResource;

class ManageAllocations extends ManageRecords
{
    protected static string $resource = AllocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon('heroicon-o-plus-circle'),
        ];
    }
}
