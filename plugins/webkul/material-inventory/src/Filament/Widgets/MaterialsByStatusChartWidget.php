<?php

namespace Webkul\MaterialInventory\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource;
use Webkul\MaterialInventory\Models\MaterialItem;

class MaterialsByStatusChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    public function getHeading(): string|Htmlable|null
    {
        return __('material-inventory::filament/widgets/materials-by-status.heading');
    }

    protected function getData(): array
    {
        $companyId = Auth::user()?->default_company_id;
        $statusOptions = MaterialItemResource::materialStatusOptions();

        $counts = MaterialItem::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->selectRaw('sheet_status, COUNT(*) as aggregate')
            ->groupBy('sheet_status')
            ->pluck('aggregate', 'sheet_status');

        $labels = [];
        $data = [];

        foreach ($statusOptions as $value => $label) {
            $labels[] = $label;
            $data[] = (int) ($counts[$value] ?? 0);
        }

        return [
            'datasets' => [[
                'label' => __('material-inventory::filament/widgets/materials-by-status.dataset'),
                'data' => $data,
                'backgroundColor' => ['#10b981', '#3b82f6', '#ef4444', '#0ea5e9', '#f59e0b'],
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

