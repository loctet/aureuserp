<?php

namespace Webkul\MaterialInventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Employee\Models\Employee;
use Webkul\MaterialInventory\Enums\MaterialTransactionType;
use Webkul\Project\Models\Project;
use Webkul\Security\Models\User;

class MaterialInventoryTransaction extends Model
{
    protected $table = 'material_inventory_transactions';

    protected $fillable = [
        'material_item_id',
        'type',
        'from_employee_id',
        'to_employee_id',
        'from_project_id',
        'to_project_id',
        'condition_before',
        'condition_after',
        'return_condition',
        'notes',
        'meta',
        'performed_by',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'meta'        => 'array',
        'type'        => MaterialTransactionType::class,
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(MaterialItem::class, 'material_item_id');
    }

    public function fromEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'from_employee_id');
    }

    public function toEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'to_employee_id');
    }

    public function fromProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'from_project_id');
    }

    public function toProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'to_project_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
