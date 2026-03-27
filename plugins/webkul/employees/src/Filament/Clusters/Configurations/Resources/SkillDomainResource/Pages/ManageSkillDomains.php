<?php

namespace Webkul\Employee\Filament\Clusters\Configurations\Resources\SkillDomainResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Webkul\Employee\Filament\Clusters\Configurations\Resources\SkillDomainResource;

class ManageSkillDomains extends ManageRecords
{
    protected static string $resource = SkillDomainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon('heroicon-o-plus-circle'),
        ];
    }
}
