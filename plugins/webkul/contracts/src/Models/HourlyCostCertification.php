<?php

namespace Webkul\Contracts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Webkul\Security\Models\User;
use Webkul\Support\Models\Currency;

class HourlyCostCertification extends Model
{
    use SoftDeletes;

    protected $table = 'contracts_hourly_cost_certifications';

    protected $fillable = [
        'contract_id',
        'currency_id',
        'certified_hourly_cost',
        'effective_from',
        'effective_to',
        'certificate_reference',
        'notes',
        'is_active',
        'creator_id',
    ];

    protected $casts = [
        'effective_from'        => 'date',
        'effective_to'          => 'date',
        'certified_hourly_cost' => 'decimal:2',
        'is_active'             => 'boolean',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $certification): void {
            $certification->creator_id ??= Auth::id();
        });
    }
}
