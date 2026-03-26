<?php

namespace Webkul\MaterialInventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Webkul\Employee\Models\Employee;
use Webkul\MaterialInventory\Enums\MaterialSheetStatus;
use Webkul\MaterialInventory\Enums\MaterialTransactionType;
use Webkul\MaterialInventory\Services\MaterialInventoryTransactionRecorder;
use Webkul\Project\Models\Project;
use Webkul\Support\Models\Company;

class MaterialItem extends Model
{
    use SoftDeletes;

    /** @internal set during updating to log project assignment */
    public mixed $materialInventoryProjectChangeFrom = null;

    protected $table = 'material_inventory_items';

    protected $fillable = [
        'company_id',
        'inventory_number',
        'inventory_number_locked',
        'progressive_asset_number',
        'name',
        'description',
        'category',
        'serial_number',
        'model',
        'manufacturer',
        'supplier',
        'acquisition_date',
        'acquisition_cost',
        'is_free',
        'sheet_status',
        'project_id',
        'current_custodian_employee_id',
        'checked_out_at',
        'assignment_date',
        'expected_return_date',
        'storage_location',
        'notes',
    ];

    protected $casts = [
        'acquisition_date'           => 'date',
        'assignment_date'            => 'date',
        'expected_return_date'       => 'date',
        'acquisition_cost'           => 'decimal:2',
        'is_free'                    => 'boolean',
        'inventory_number_locked'    => 'boolean',
        'checked_out_at'             => 'datetime',
        'sheet_status'               => MaterialSheetStatus::class,
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function currentCustodian(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'current_custodian_employee_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MaterialInventoryTransaction::class, 'material_item_id')
            ->orderByDesc('occurred_at');
    }

    public function isDraftInventoryNumber(): bool
    {
        return str_starts_with((string) $this->inventory_number, 'DRAFT-');
    }

    protected static function booted(): void
    {
        static::created(function (MaterialItem $item): void {
            if (! $item->project_id) {
                return;
            }

            MaterialInventoryTransactionRecorder::record(
                $item,
                MaterialTransactionType::AssignProject,
                [
                    'to_project_id' => $item->project_id,
                ],
            );
        });

        static::updating(function (MaterialItem $item): void {
            if ($item->isDirty('project_id')) {
                $item->materialInventoryProjectChangeFrom = $item->getOriginal('project_id');
            }
        });

        static::updated(function (MaterialItem $item): void {
            if (! $item->wasChanged('project_id')) {
                return;
            }

            $from = $item->materialInventoryProjectChangeFrom;
            $item->materialInventoryProjectChangeFrom = null;

            MaterialInventoryTransactionRecorder::record(
                $item,
                MaterialTransactionType::AssignProject,
                [
                    'from_project_id' => $from,
                    'to_project_id'   => $item->project_id,
                ],
            );
        });
    }
}
