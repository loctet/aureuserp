<?php

namespace Webkul\Contracts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Webkul\Employee\Models\Employee;
use Webkul\Security\Models\User;

class Contract extends Model
{
    use SoftDeletes;

    protected $table = 'contracts_contracts';

    protected $fillable = [
        'employee_id',
        'contract_type_id',
        'start_date',
        'end_date',
        'renewal_deadline',
        'status',
        'reference',
        'notes',
        'creator_id',
    ];

    protected $casts = [
        'start_date'       => 'date',
        'end_date'         => 'date',
        'renewal_deadline' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class, 'contract_type_id');
    }

    public function hourlyCostCertifications(): HasMany
    {
        return $this->hasMany(HourlyCostCertification::class, 'contract_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $contract): void {
            $contract->creator_id ??= Auth::id();
        });
    }
}
