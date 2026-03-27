<?php

namespace Webkul\Contracts\Services;

use Illuminate\Support\Collection;
use Webkul\Contracts\Models\Contract;
use Webkul\Employee\Models\Employee;

class AllocatablePoolQueryService
{
    public function forMonth(string $month): Collection
    {
        return Employee::query()
            ->with([
                'skills.skill',
                'skills.skillLevel',
                'monthlyAvailability' => fn ($query) => $query->whereDate('month', $month),
                'contracts.hourlyCostCertifications' => function ($query) use ($month): void {
                    $query->where('is_active', true)
                        ->whereDate('effective_from', '<=', $month)
                        ->where(function ($inner) use ($month): void {
                            $inner->whereNull('effective_to')
                                ->orWhereDate('effective_to', '>=', $month);
                        });
                },
            ])
            ->get()
            ->map(function (Employee $employee) use ($month): array {
                $availability = $employee->monthlyAvailability->first();
                $remainingFte = app(AvailabilityBalanceService::class)->remainingFtePercent($employee->getKey(), $month);
                $remainingPm = app(AvailabilityBalanceService::class)->remainingPersonMonths($employee->getKey(), $month);

                $hourlyRate = optional(
                    $employee->contracts
                        ->first(fn (Contract $contract) => $contract->status === 'active')
                        ?->hourlyCostCertifications
                        ->sortByDesc('effective_from')
                        ->first()
                )->certified_hourly_cost;

                return [
                    'employee_id'             => $employee->getKey(),
                    'employee_name'           => $employee->name,
                    'skills'                  => $employee->skills
                        ->where('validation_status', 'validated')
                        ->map(fn ($skill) => [
                            'skill_id'     => $skill->skill_id,
                            'skill'        => $skill->skill?->name,
                            'proficiency'  => $skill->proficiency,
                            'skill_level'  => $skill->skillLevel?->name,
                            'validated_at' => optional($skill->validated_at)?->toDateTimeString(),
                        ])
                        ->values()
                        ->all(),
                    'month'                   => $month,
                    'availability_fte_percent'=> (float) ($availability?->fte_percent ?? 0),
                    'remaining_fte_percent'   => $remainingFte,
                    'person_months'           => (float) ($availability?->person_months ?? 0),
                    'person_months_remaining' => $remainingPm,
                    'certified_hourly_cost'   => $hourlyRate !== null ? (float) $hourlyRate : null,
                ];
            });
    }
}
