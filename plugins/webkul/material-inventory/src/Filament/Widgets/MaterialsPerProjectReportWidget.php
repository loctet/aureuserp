<?php

namespace Webkul\MaterialInventory\Filament\Widgets;

use Filament\Tables;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Webkul\Project\Models\Project;

class MaterialsPerProjectReportWidget extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return __('material-inventory::filament/widgets/materials-per-project.heading');
    }

    protected function getTableQuery(): Builder
    {
        $companyId = Auth::user()?->default_company_id;

        return Project::query()
            ->when($companyId, fn (Builder $query) => $query->where('projects_projects.company_id', $companyId))
            ->leftJoin('material_inventory_items as mi', function ($join) {
                $join->on('mi.project_id', '=', 'projects_projects.id')
                    ->whereNull('mi.deleted_at');
            })
            ->select([
                'projects_projects.id',
                'projects_projects.name',
                'projects_projects.budget',
            ])
            ->selectRaw('COUNT(mi.id) as materials_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN mi.is_free = 0 THEN mi.acquisition_cost ELSE 0 END), 0) as materials_spend')
            ->groupBy('projects_projects.id', 'projects_projects.name', 'projects_projects.budget')
            ->orderByDesc('materials_spend')
            ->limit(15);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label(__('material-inventory::filament/widgets/materials-per-project.columns.project'))
                ->searchable(),
            Tables\Columns\TextColumn::make('materials_count')
                ->label(__('material-inventory::filament/widgets/materials-per-project.columns.materials_count'))
                ->badge()
                ->color('primary'),
            Tables\Columns\TextColumn::make('materials_spend')
                ->label(__('material-inventory::filament/widgets/materials-per-project.columns.materials_spend'))
                ->money('EUR'),
            Tables\Columns\TextColumn::make('budget')
                ->label(__('material-inventory::filament/widgets/materials-per-project.columns.budget'))
                ->money('EUR'),
            Tables\Columns\TextColumn::make('remaining_budget')
                ->label(__('material-inventory::filament/widgets/materials-per-project.columns.remaining_budget'))
                ->state(fn ($record): float => (float) ($record->budget ?? 0) - (float) ($record->materials_spend ?? 0))
                ->money('EUR')
                ->color(fn ($record): string => ((float) ($record->budget ?? 0) - (float) ($record->materials_spend ?? 0)) < 0 ? 'danger' : 'success'),
        ];
    }
}

