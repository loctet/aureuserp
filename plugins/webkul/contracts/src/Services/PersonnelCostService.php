<?php

namespace Webkul\Contracts\Services;

use Carbon\Carbon;
use Webkul\Contracts\Models\Contract;
use Webkul\Contracts\Models\HourlyCostCertification;

class PersonnelCostService
{
    public function resolveHourlyRate(Contract $contract, string $workDate): ?float
    {
        $date = Carbon::parse($workDate)->toDateString();

        $certification = HourlyCostCertification::query()
            ->where('contract_id', $contract->getKey())
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $date)
            ->where(function ($query) use ($date): void {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $date);
            })
            ->orderByDesc('effective_from')
            ->first();

        return $certification ? (float) $certification->certified_hourly_cost : null;
    }

    public function calculatePersonnelCost(Contract $contract, float $actualHours, string $workDate): float
    {
        $hourlyRate = $this->resolveHourlyRate($contract, $workDate) ?? 0;

        return round($actualHours * $hourlyRate, 2);
    }
}
