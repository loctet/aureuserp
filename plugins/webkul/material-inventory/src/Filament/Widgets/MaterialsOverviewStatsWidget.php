<?php

namespace Webkul\MaterialInventory\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Webkul\MaterialInventory\Enums\MaterialSheetStatus;
use Webkul\MaterialInventory\Models\MaterialItem;

class MaterialsOverviewStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '20s';

    protected function getHeading(): ?string
    {
        return __('material-inventory::filament/widgets/materials-overview.heading');
    }

    protected function getStats(): array
    {
        $companyId = Auth::user()?->default_company_id;
        $baseQuery = MaterialItem::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId));

        $total = (clone $baseQuery)->count();
        $assigned = (clone $baseQuery)->whereNotNull('current_custodian_employee_id')->count();
        $underRepair = (clone $baseQuery)->where('sheet_status', MaterialSheetStatus::InRiparazione)->count();

        $nonFunctional = MaterialItem::hasFunctionalColumn()
            ? (clone $baseQuery)->where('is_functional', false)->count()
            : (clone $baseQuery)->where('sheet_status', MaterialSheetStatus::Guasto)->count();

        $available = (clone $baseQuery)
            ->whereNull('current_custodian_employee_id')
            ->where('sheet_status', '!=', MaterialSheetStatus::InRiparazione)
            ->when(
                MaterialItem::hasFunctionalColumn(),
                fn ($query) => $query->where('is_functional', true)
            )
            ->count();

        return [
            Stat::make(__('material-inventory::filament/widgets/materials-overview.stats.total'), $total)
                ->description(__('material-inventory::filament/widgets/materials-overview.stats.total_desc'))
                ->color('primary'),
            Stat::make(__('material-inventory::filament/widgets/materials-overview.stats.available'), $available)
                ->description(__('material-inventory::filament/widgets/materials-overview.stats.available_desc'))
                ->color('success'),
            Stat::make(__('material-inventory::filament/widgets/materials-overview.stats.assigned'), $assigned)
                ->description(__('material-inventory::filament/widgets/materials-overview.stats.assigned_desc'))
                ->color('info'),
            Stat::make(__('material-inventory::filament/widgets/materials-overview.stats.under_repair'), $underRepair)
                ->description(__('material-inventory::filament/widgets/materials-overview.stats.under_repair_desc'))
                ->color('warning'),
            Stat::make(__('material-inventory::filament/widgets/materials-overview.stats.non_functional'), $nonFunctional)
                ->description(__('material-inventory::filament/widgets/materials-overview.stats.non_functional_desc'))
                ->color('danger'),
        ];
    }
}

