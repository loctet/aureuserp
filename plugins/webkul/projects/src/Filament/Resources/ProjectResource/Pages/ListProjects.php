<?php

namespace Webkul\Project\Filament\Resources\ProjectResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Webkul\BulkImport\Filament\Actions\BulkCsvActions;
use Webkul\Project\Filament\Resources\ProjectResource;
use Webkul\Project\Models\Project;
use Webkul\TableViews\Filament\Components\PresetView;
use Webkul\TableViews\Filament\Concerns\HasTableViews;

class ListProjects extends ListRecords
{
    use HasTableViews;

    protected static string $resource = ProjectResource::class;

    public function getPresetTableViews(): array
    {
        return [
            'my_projects' => PresetView::make(__('projects::filament/resources/project/pages/list-projects.tabs.my-projects'))
                ->icon('heroicon-s-user')
                ->favorite()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('user_id', Auth::id())),

            'my_favorite_projects' => PresetView::make(__('projects::filament/resources/project/pages/list-projects.tabs.my-favorite-projects'))
                ->icon('heroicon-s-star')
                ->favorite()
                ->modifyQueryUsing(function (Builder $query) {
                    return $query
                        ->leftJoin('projects_user_project_favorites', 'projects_user_project_favorites.project_id', '=', 'projects_projects.id')
                        ->where('projects_user_project_favorites.user_id', Auth::id());
                }),

            'unassigned_projects' => PresetView::make(__('projects::filament/resources/project/pages/list-projects.tabs.unassigned-projects'))
                ->icon('heroicon-s-user-minus')
                ->favorite()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('user_id')),

            'archived_projects' => PresetView::make(__('projects::filament/resources/project/pages/list-projects.tabs.archived-projects'))
                ->icon('heroicon-s-archive-box')
                ->favorite()
                ->modifyQueryUsing(function ($query) {
                    return $query->onlyTrashed();
                }),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            BulkCsvActions::makeImportAction(
                Project::class,
                [
                    'name'             => 'string',
                    'description'      => 'string',
                    'visibility'       => 'string',
                    'company_id'       => 'int',
                    'user_id'          => 'int',
                    'partner_id'       => 'int',
                    'stage_id'         => 'int',
                    'start_date'       => 'string',
                    'end_date'         => 'string',
                    'allocated_hours'  => 'float',
                    'budget'           => 'float',
                    'allow_timesheets' => 'bool',
                    'allow_milestones' => 'bool',
                ],
            ),
            BulkCsvActions::makeTemplateAction(
                'projects-template.csv',
                ['name', 'description', 'visibility', 'company_id', 'user_id', 'partner_id', 'stage_id', 'start_date', 'end_date', 'allocated_hours', 'budget', 'allow_timesheets', 'allow_milestones'],
                [
                    'name'             => 'Website Revamp',
                    'description'      => 'Main website modernization project',
                    'visibility'       => 'internal',
                    'company_id'       => 1,
                    'user_id'          => 1,
                    'partner_id'       => 1,
                    'stage_id'         => 1,
                    'start_date'       => '2026-04-01',
                    'end_date'         => '2026-06-30',
                    'allocated_hours'  => 240,
                    'budget'           => 10000,
                    'allow_timesheets' => 'true',
                    'allow_milestones' => 'true',
                ],
            ),
            CreateAction::make()
                ->label(__('projects::filament/resources/project/pages/list-projects.header-actions.create.label'))
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
