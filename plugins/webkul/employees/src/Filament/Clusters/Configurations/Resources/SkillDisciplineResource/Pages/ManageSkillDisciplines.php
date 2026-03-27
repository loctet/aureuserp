<?php

namespace Webkul\Employee\Filament\Clusters\Configurations\Resources\SkillDisciplineResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Webkul\Employee\Filament\Clusters\Configurations\Resources\SkillDisciplineResource;

class ManageSkillDisciplines extends ManageRecords
{
    protected static string $resource = SkillDisciplineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon('heroicon-o-plus-circle'),
        ];
    }
}
