<?php

namespace Webkul\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Webkul\Security\Models\User;

class SkillDiscipline extends Model
{
    use SoftDeletes;

    protected $table = 'employees_skill_disciplines';

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'skill_domain_id',
        'creator_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(SkillDomain::class, 'skill_domain_id');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class, 'skill_discipline_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $discipline): void {
            $discipline->creator_id ??= Auth::id();
        });
    }
}
