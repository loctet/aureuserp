<?php

namespace Webkul\Project\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Webkul\Project\Filament\Clusters\Configurations\Resources\WorkPackageResource;

class WorkPackagesRelationManager extends RelationManager
{
    protected static string $relationship = 'workPackages';

    public function form(Schema $schema): Schema
    {
        return WorkPackageResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return WorkPackageResource::table($table)
            ->filters([])
            ->groups([])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus-circle'),
            ]);
    }
}
