<?php

namespace Webkul\Contracts\Filament\Resources\ContractResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Webkul\Contracts\Filament\Resources\HourlyCostCertificationResource;

class HourlyCostCertificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'hourlyCostCertifications';

    public function form(Schema $schema): Schema
    {
        return HourlyCostCertificationResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return HourlyCostCertificationResource::table($table)
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus-circle'),
            ]);
    }
}
