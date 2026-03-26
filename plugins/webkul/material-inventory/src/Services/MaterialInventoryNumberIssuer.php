<?php

namespace Webkul\MaterialInventory\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\MaterialInventory\Models\MaterialItem;

final class MaterialInventoryNumberIssuer
{
    public static function draftInventoryNumber(): string
    {
        return 'DRAFT-'.strtoupper(bin2hex(random_bytes(6)));
    }

    /**
     * Category labels in the sheet use "{Letter}-{name}", e.g. "N-Notebook".
     */
    public static function categoryLetter(?string $category): string
    {
        $category = trim((string) $category);
        if ($category === '') {
            return 'X';
        }

        if (preg_match('/^([A-Za-z])\s*-/u', $category, $m)) {
            return strtoupper($m[1]);
        }

        return strtoupper(mb_substr($category, 0, 1));
    }

    public static function issueFormalId(MaterialItem $item): void
    {
        if ($item->inventory_number_locked) {
            return;
        }

        DB::transaction(function () use ($item): void {
            $reserved = self::reserveIdentifier(
                companyId: (int) $item->company_id,
                category: $item->category,
                acquisitionDate: $item->acquisition_date?->toDateString(),
            );

            $item->progressive_asset_number = $reserved['progressive_asset_number'];
            $item->inventory_number = $reserved['inventory_number'];
            $item->inventory_number_locked = true;
            $item->save();
        });
    }

    /**
     * Reserve the next unique ID atomically per company.
     *
     * @return array{progressive_asset_number:int,inventory_number:string}
     */
    public static function reserveIdentifier(int $companyId, ?string $category, mixed $acquisitionDate = null): array
    {
        return DB::transaction(function () use ($companyId, $category, $acquisitionDate): array {
            $year = $acquisitionDate ? Carbon::parse($acquisitionDate)->year : now()->year;

            $nextProgressive = (int) MaterialItem::query()
                ->where('company_id', $companyId)
                ->whereNotNull('progressive_asset_number')
                ->lockForUpdate()
                ->max('progressive_asset_number') + 1;

            if ($nextProgressive < 1) {
                $nextProgressive = 1;
            }

            $prefix = self::categoryLetter($category);

            return [
                'progressive_asset_number' => $nextProgressive,
                'inventory_number'         => sprintf('%s-%04d-%d', $prefix, $nextProgressive, $year),
            ];
        });
    }
}
