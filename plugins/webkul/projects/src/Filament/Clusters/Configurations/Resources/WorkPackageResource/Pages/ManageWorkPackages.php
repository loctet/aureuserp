<?php

namespace Webkul\Project\Filament\Clusters\Configurations\Resources\WorkPackageResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Webkul\Project\Filament\Clusters\Configurations\Resources\WorkPackageResource;

class ManageWorkPackages extends ManageRecords
{
    protected static string $resource = WorkPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
