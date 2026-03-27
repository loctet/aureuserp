<?php

namespace Webkul\Employee\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Webkul\Employee\Filament\Clusters\Reportings\Resources\EmployeeMonthlyAvailabilityResource;

class MonthlyAvailabilityRelationManager extends RelationManager
{
    protected static string $relationship = 'monthlyAvailability';

    public function form(Schema $schema): Schema
    {
        return EmployeeMonthlyAvailabilityResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return EmployeeMonthlyAvailabilityResource::table($table)
            ->filters([])
            ->groups([])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus-circle'),
            ]);
    }
}
