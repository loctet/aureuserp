<?php

namespace Webkul\Contracts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Webkul\Employee\Models\Employee;
use Webkul\Project\Models\Project;
use Webkul\Project\Models\WorkPackage;
use Webkul\Security\Models\User;

class Allocation extends Model
{
    use SoftDeletes;

    protected $table = 'contracts_allocations';

    protected $fillable = [
        'employee_id',
        'project_id',
        'work_package_id',
        'month',
        'fte_percent',
        'person_months',
        'notes',
        'creator_id',
    ];

    protected $casts = [
        'month'         => 'date',
        'fte_percent'   => 'decimal:2',
        'person_months' => 'decimal:4',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function workPackage(): BelongsTo
    {
        return $this->belongsTo(WorkPackage::class, 'work_package_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $allocation): void {
            $allocation->creator_id ??= Auth::id();
        });

        static::saving(function (self $allocation): void {
            if (! $allocation->employee_id || ! $allocation->month) {
                return;
            }

            $month = $allocation->month instanceof \DateTimeInterface
                ? $allocation->month->format('Y-m-d')
                : (string) $allocation->month;

            $allocatedWithoutCurrent = static::query()
                ->where('employee_id', $allocation->employee_id)
                ->whereDate('month', $month)
                ->when($allocation->exists, fn ($query) => $query->whereKeyNot($allocation->getKey()))
                ->sum('fte_percent');

            $available = (float) \Webkul\Employee\Models\EmployeeMonthlyAvailability::query()
                ->where('employee_id', $allocation->employee_id)
                ->whereDate('month', $month)
                ->value('fte_percent');

            if ($available <= 0) {
                throw ValidationException::withMessages([
                    'month' => sprintf(
                        'Monthly availability is missing for employee %d in %s.',
                        $allocation->employee_id,
                        $month
                    ),
                ]);
            }

            $projectedTotal = (float) $allocatedWithoutCurrent + (float) $allocation->fte_percent;

            if ($available > 0 && $projectedTotal > $available) {
                throw ValidationException::withMessages([
                    'fte_percent' => sprintf(
                        'Allocation exceeds available FTE for %s (requested %.2f, available %.2f).',
                        $month,
                        $projectedTotal,
                        $available
                    ),
                ]);
            }
        });
    }
}
