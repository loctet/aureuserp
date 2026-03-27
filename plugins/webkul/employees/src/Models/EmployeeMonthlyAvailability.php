<?php

namespace Webkul\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Webkul\Security\Models\User;

class EmployeeMonthlyAvailability extends Model
{
    use SoftDeletes;

    protected $table = 'employees_monthly_availability';

    protected $fillable = [
        'employee_id',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $availability): void {
            $availability->creator_id ??= Auth::id();
        });
    }
}
