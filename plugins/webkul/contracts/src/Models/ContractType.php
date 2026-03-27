<?php

namespace Webkul\Contracts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Webkul\Security\Models\User;

class ContractType extends Model
{
    use SoftDeletes;

    protected $table = 'contracts_contract_types';

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'creator_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'contract_type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $type): void {
            $type->creator_id ??= Auth::id();
        });
    }
}
