<?php

namespace Webkul\Employee\Filament\Clusters\Reportings\Resources\EmployeeMonthlyAvailabilityResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Webkul\Employee\Filament\Clusters\Reportings\Resources\EmployeeMonthlyAvailabilityResource;

class ManageEmployeeMonthlyAvailabilities extends ManageRecords
{
    protected static string $resource = EmployeeMonthlyAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon('heroicon-o-plus-circle'),
        ];
    }
}
