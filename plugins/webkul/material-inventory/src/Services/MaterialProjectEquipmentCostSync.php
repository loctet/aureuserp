<?php

namespace Webkul\MaterialInventory\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class MaterialProjectEquipmentCostSync
{
    public static function syncProject(?int $projectId): void
    {
        if (! $projectId) {
            return;
        }

        if (
            ! Schema::hasTable('projects_projects')
            || ! Schema::hasTable('material_inventory_items')
            || ! Schema::hasColumn('projects_projects', 'budget_purchase_equipment_spent')
        ) {
            return;
        }

        $spent = (float) DB::table('material_inventory_items')
            ->where('project_id', $projectId)
            ->where('is_free', false)
            ->whereNull('deleted_at')
            ->sum('acquisition_cost');

        DB::table('projects_projects')
            ->where('id', $projectId)
            ->update([
                'budget_purchase_equipment_spent' => $spent,
                'updated_at'                     => now(),
            ]);
    }
}
