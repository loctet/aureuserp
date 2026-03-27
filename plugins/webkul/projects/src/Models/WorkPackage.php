<?php

namespace Webkul\Project\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Webkul\Security\Models\User;

class WorkPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'projects_work_packages';

    protected $fillable = [
        'project_id',
        'name',
        'code',
        'description',
        'start_date',
        'end_date',
        'is_active',
        'creator_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $workPackage): void {
            $workPackage->creator_id ??= Auth::id();
        });
    }
}
