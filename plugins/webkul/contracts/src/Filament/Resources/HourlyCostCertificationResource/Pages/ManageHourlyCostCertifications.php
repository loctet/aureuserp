<?php

namespace Webkul\Contracts\Filament\Resources\HourlyCostCertificationResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Webkul\Contracts\Filament\Resources\HourlyCostCertificationResource;

class ManageHourlyCostCertifications extends ManageRecords
{
    protected static string $resource = HourlyCostCertificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon('heroicon-o-plus-circle'),
        ];
    }
}
