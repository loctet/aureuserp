<?php

namespace Webkul\MaterialInventory\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Webkul\MaterialInventory\Models\MaterialItem;

class MaterialsByCategoryChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '280px';

    public function getHeading(): string|Htmlable|null
    {
        return __('material-inventory::filament/widgets/materials-by-category.heading');
    }

    protected function getData(): array
    {
        $companyId = Auth::user()?->default_company_id;

        $rows = MaterialItem::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->selectRaw('category, COUNT(*) as aggregate')
            ->groupBy('category')
            ->orderByDesc('aggregate')
            ->limit(8)
            ->get();

        return [
            'datasets' => [[
                'label' => __('material-inventory::filament/widgets/materials-by-category.dataset'),
                'data' => $rows->pluck('aggregate')->map(fn ($v) => (int) $v)->values()->all(),
                'backgroundColor' => '#6366f1',
            ]],
            'labels' => $rows->pluck('category')->map(fn ($v) => (string) $v)->values()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

