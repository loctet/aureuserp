<?php

namespace Webkul\Project\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Webkul\Security\Models\User;

class ProjectRequiredSkill extends Model
{
    use SoftDeletes;

    protected $table = 'projects_project_required_skills';

    protected $fillable = [
        'project_id',
        'skill_domain_id',
        'skill_discipline_id',
        'skill_id',
        'proficiency',
        'required_fte_percent',
        'required_person_months',
        'notes',
        'creator_id',
    ];

    protected $casts = [
        'required_fte_percent'   => 'decimal:2',
        'required_person_months' => 'decimal:4',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Employee\Models\Skill::class, 'skill_id');
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Employee\Models\SkillDomain::class, 'skill_domain_id');
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Employee\Models\SkillDiscipline::class, 'skill_discipline_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $requiredSkill): void {
            $requiredSkill->creator_id ??= Auth::id();
        });
    }
}
