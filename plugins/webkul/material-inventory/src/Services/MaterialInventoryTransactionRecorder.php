<?php

namespace Webkul\MaterialInventory\Services;

use Illuminate\Support\Facades\Auth;
use Webkul\MaterialInventory\Enums\MaterialTransactionType;
use Webkul\MaterialInventory\Models\MaterialInventoryTransaction;
use Webkul\MaterialInventory\Models\MaterialItem;

final class MaterialInventoryTransactionRecorder
{
    public static function record(
        MaterialItem $item,
        MaterialTransactionType $type,
        array $attributes = []
    ): MaterialInventoryTransaction {
        return $item->transactions()->create(array_merge([
            'occurred_at'   => now(),
            'performed_by'  => Auth::id(),
        ], $attributes, [
            'type' => $type->value,
        ]));
    }
}
