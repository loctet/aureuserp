<?php

namespace Webkul\Contracts\Services;

use Webkul\Contracts\Models\Allocation;
use Webkul\Employee\Models\EmployeeMonthlyAvailability;

class AvailabilityBalanceService
{
    public function remainingFtePercent(int $employeeId, string $month): float
    {
        $available = (float) EmployeeMonthlyAvailability::query()
            ->where('employee_id', $employeeId)
            ->whereDate('month', $month)
            ->value('fte_percent');

        $allocated = (float) Allocation::query()
            ->where('employee_id', $employeeId)
            ->whereDate('month', $month)
            ->sum('fte_percent');

        return max(0, round($available - $allocated, 2));
    }

    public function remainingPersonMonths(int $employeeId, string $month): float
    {
        $available = (float) EmployeeMonthlyAvailability::query()
            ->where('employee_id', $employeeId)
            ->whereDate('month', $month)
            ->value('person_months');

        $allocated = (float) Allocation::query()
            ->where('employee_id', $employeeId)
            ->whereDate('month', $month)
            ->sum('person_months');

        return max(0, round($available - $allocated, 4));
    }
}
