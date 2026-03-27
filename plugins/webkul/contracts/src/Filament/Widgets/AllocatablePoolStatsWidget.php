<?php

namespace Webkul\Contracts\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Webkul\Contracts\Services\AllocatablePoolQueryService;

class AllocatablePoolStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $month = now()->startOfMonth()->toDateString();
        $rows = app(AllocatablePoolQueryService::class)->forMonth($month);

        $totalEmployees = $rows->count();
        $totalRemainingFte = (float) $rows->sum('remaining_fte_percent');
        $totalRemainingPm = (float) $rows->sum('person_months_remaining');

        return [
            Stat::make('People in pool', (string) $totalEmployees),
            Stat::make('Remaining FTE %', number_format($totalRemainingFte, 2)),
            Stat::make('Remaining person-months', number_format($totalRemainingPm, 4)),
        ];
    }
}
