<?php

namespace Webkul\MaterialInventory\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Webkul\MaterialInventory\Enums\MaterialTransactionType;
use Webkul\MaterialInventory\Models\MaterialInventoryTransaction;

class InventoryLifecycleChartWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    public function getHeading(): string|Htmlable|null
    {
        return __('material-inventory::filament/widgets/inventory-lifecycle.heading');
    }

    protected function getData(): array
    {
        $companyId = Auth::user()?->default_company_id;
        $labels = collect(range(5, 1))->map(fn (int $monthsAgo) => now()->subMonths($monthsAgo)->format('M Y'));
        $labels->push(now()->format('M Y'));

        $series = [
            MaterialTransactionType::CheckOut->value => [],
            MaterialTransactionType::CheckIn->value => [],
            MaterialTransactionType::SendRepair->value => [],
            MaterialTransactionType::ReturnFromRepair->value => [],
        ];

        foreach ($labels as $label) {
            $date = Carbon::createFromFormat('M Y', $label);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            $base = MaterialInventoryTransaction::query()
                ->when($companyId, function ($query) use ($companyId) {
                    $query->whereHas('item', fn ($q) => $q->where('company_id', $companyId));
                })
                ->whereBetween('occurred_at', [$start, $end]);

            foreach (array_keys($series) as $type) {
                $series[$type][] = (clone $base)->where('type', $type)->count();
            }
        }

        return [
            'datasets' => [
                [
                    'label' => __('material-inventory::filament/widgets/inventory-lifecycle.datasets.check_out'),
                    'data' => $series[MaterialTransactionType::CheckOut->value],
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.2)',
                ],
                [
                    'label' => __('material-inventory::filament/widgets/inventory-lifecycle.datasets.check_in'),
                    'data' => $series[MaterialTransactionType::CheckIn->value],
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                ],
                [
                    'label' => __('material-inventory::filament/widgets/inventory-lifecycle.datasets.send_repair'),
                    'data' => $series[MaterialTransactionType::SendRepair->value],
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                ],
                [
                    'label' => __('material-inventory::filament/widgets/inventory-lifecycle.datasets.return_repair'),
                    'data' => $series[MaterialTransactionType::ReturnFromRepair->value],
                    'borderColor' => '#8b5cf6',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.2)',
                ],
            ],
            'labels' => $labels->values()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

