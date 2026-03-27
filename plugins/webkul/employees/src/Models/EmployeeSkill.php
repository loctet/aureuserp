<?php

namespace Webkul\Employee\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Webkul\Employee\Database\Factories\EmployeeSkillFactory;
use Webkul\Security\Models\User;

class EmployeeSkill extends Model
{
    use HasFactory, SoftDeletes;

    public const PROFICIENCY_BASIC = 'basic';
    public const PROFICIENCY_INTERMEDIATE = 'intermediate';
    public const PROFICIENCY_ADVANCED = 'advanced';
    public const PROFICIENCY_EXPERT = 'expert';

    public const VALIDATION_PENDING = 'pending';
    public const VALIDATION_VALIDATED = 'validated';
    public const VALIDATION_REJECTED = 'rejected';

    protected $table = 'employees_employee_skills';

    protected $fillable = [
        'employee_id',
        'skill_id',
        'skill_level_id',
        'skill_type_id',
        'proficiency',
        'validation_status',
        'validated_by',
        'validated_at',
        'validation_notes',
        'creator_id',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function skillLevel()
    {
        return $this->belongsTo(SkillLevel::class);
    }

    public function skillType()
    {
        return $this->belongsTo(SkillType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'validated_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employeeSkill) {
            $employeeSkill->creator_id ??= Auth::id();
        });

        static::saving(function (self $employeeSkill): void {
            $allowedProficiency = [
                self::PROFICIENCY_BASIC,
                self::PROFICIENCY_INTERMEDIATE,
                self::PROFICIENCY_ADVANCED,
                self::PROFICIENCY_EXPERT,
            ];

            if ($employeeSkill->proficiency && ! in_array($employeeSkill->proficiency, $allowedProficiency, true)) {
                throw ValidationException::withMessages([
                    'proficiency' => 'Invalid proficiency value.',
                ]);
            }

            $allowedValidationStates = [
                self::VALIDATION_PENDING,
                self::VALIDATION_VALIDATED,
                self::VALIDATION_REJECTED,
            ];

            if (
                $employeeSkill->validation_status
                && ! in_array($employeeSkill->validation_status, $allowedValidationStates, true)
            ) {
                throw ValidationException::withMessages([
                    'validation_status' => 'Invalid validation status value.',
                ]);
            }

            $requiresValidator = in_array(
                $employeeSkill->validation_status,
                [self::VALIDATION_VALIDATED, self::VALIDATION_REJECTED],
                true
            );

            if ($requiresValidator && ! $employeeSkill->validated_by) {
                throw ValidationException::withMessages([
                    'validated_by' => 'Area manager is required when validating/rejecting a skill.',
                ]);
            }

            if ($requiresValidator && $employeeSkill->employee_id && $employeeSkill->validated_by) {
                $employee = Employee::query()->with('department')->find($employeeSkill->employee_id);
                $expectedManagerId = $employee?->department?->manager_id;

                if ($expectedManagerId && (int) $employeeSkill->validated_by !== (int) $expectedManagerId) {
                    throw ValidationException::withMessages([
                        'validated_by' => 'Validation must be performed by the employee area manager.',
                    ]);
                }
            }

            if ($employeeSkill->validation_status === self::VALIDATION_VALIDATED) {
                $employeeSkill->validated_at ??= now();
            }
        });
    }

    protected static function newFactory(): EmployeeSkillFactory
    {
        return EmployeeSkillFactory::new();
    }
}
