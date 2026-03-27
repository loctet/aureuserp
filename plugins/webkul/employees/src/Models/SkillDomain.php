<?php

namespace Webkul\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Webkul\Security\Models\User;

class SkillDomain extends Model
{
    use SoftDeletes;

    protected $table = 'employees_skill_domains';

    protected $fillable = [
        'name',
        'color',
        'is_active',
        'creator_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function disciplines(): HasMany
    {
        return $this->hasMany(SkillDiscipline::class, 'skill_domain_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $domain): void {
            $domain->creator_id ??= Auth::id();
        });
    }
}
