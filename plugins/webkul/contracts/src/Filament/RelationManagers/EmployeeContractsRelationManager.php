<?php

namespace Webkul\Contracts\Filament\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Webkul\Contracts\Filament\Resources\ContractResource;

class EmployeeContractsRelationManager extends RelationManager
{
    protected static string $relationship = 'contracts';

    public function form(Schema $schema): Schema
    {
        return ContractResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return ContractResource::table($table)
            ->filters([])
            ->groups([])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus-circle'),
            ]);
    }
}
