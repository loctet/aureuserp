<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('material_inventory_items', 'sheet_status')) {
            Schema::table('material_inventory_items', function (Blueprint $table) {
                $table->boolean('inventory_number_locked')->default(false)->after('inventory_number');
                $table->unsignedInteger('progressive_asset_number')->nullable()->after('inventory_number_locked');
                $table->string('supplier')->nullable()->after('manufacturer');
                $table->date('assignment_date')->nullable()->after('checked_out_at');
                $table->date('expected_return_date')->nullable()->after('assignment_date');
                $table->string('sheet_status')->default('nuovo');
            });

            if (Schema::hasColumn('material_inventory_items', 'lifecycle_status')) {
                foreach (DB::table('material_inventory_items')->cursor() as $row) {
                    $sheetStatus = $this->mapLegacyToSheetStatus(
                        $row->lifecycle_status ?? null,
                        $row->condition ?? null
                    );

                    $progressive = $this->parseProgressiveFromInventoryNumber($row->inventory_number ?? '');

                    DB::table('material_inventory_items')->where('id', $row->id)->update([
                        'sheet_status'             => $sheetStatus,
                        'progressive_asset_number' => $progressive,
                    ]);
                }

                Schema::table('material_inventory_items', function (Blueprint $table) {
                    $table->dropColumn(['condition', 'lifecycle_status']);
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('material_inventory_items', 'sheet_status')) {
            Schema::table('material_inventory_items', function (Blueprint $table) {
                $table->string('condition')->default('good');
                $table->string('lifecycle_status')->default('in_storage');
            });

            Schema::table('material_inventory_items', function (Blueprint $table) {
                $table->dropColumn([
                    'inventory_number_locked',
                    'progressive_asset_number',
                    'supplier',
                    'assignment_date',
                    'expected_return_date',
                    'sheet_status',
                ]);
            });
        }
    }

    private function mapLegacyToSheetStatus(?string $lifecycle, ?string $condition): string
    {
        if ($lifecycle === 'checked_out') {
            return 'in_uso';
        }

        if ($lifecycle === 'in_repair' || $condition === 'broken') {
            return 'guasto';
        }

        if ($lifecycle === 'lost' || $lifecycle === 'retired') {
            return 'usato';
        }

        if ($condition === 'new') {
            return 'nuovo';
        }

        return 'usato';
    }

    private function parseProgressiveFromInventoryNumber(string $inventoryNumber): ?int
    {
        if (preg_match('/^[A-Z]-(\d{4})-(\d{4})$/i', trim($inventoryNumber), $m)) {
            return (int) $m[1];
        }

        return null;
    }
};
