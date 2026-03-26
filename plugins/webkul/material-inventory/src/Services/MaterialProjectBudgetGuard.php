<?php

namespace Webkul\MaterialInventory\Services;

use Webkul\MaterialInventory\Models\MaterialItem;
use Webkul\Project\Models\Project;

/**
 * Ensures a project can absorb the economic cost of an item when assigning {@see MaterialItem::$project_id}.
 * Free items always pass. If the project has no budget set (null), the check passes (treat as unconstrained).
 */
final class MaterialProjectBudgetGuard
{
    public static function canAssignItemToProject(Project $project, MaterialItem $item): bool
    {
        if ($item->is_free) {
            return true;
        }

        $cost = (float) ($item->acquisition_cost ?? 0);

        if ($cost <= 0 || $project->budget === null) {
            return true;
        }

        $budget = (float) $project->budget;

        $query = MaterialItem::query()
            ->where('project_id', $project->getKey())
            ->where('is_free', false);

        if ($item->exists) {
            $query->whereKeyNot($item->getKey());
        }

        $otherAllocated = (float) $query->sum('acquisition_cost');

        return ($otherAllocated + $cost) <= $budget;
    }
}
