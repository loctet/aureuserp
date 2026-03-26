<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Webkul\MaterialInventory\Enums\MaterialSheetStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_inventory_items', function (Blueprint $table) {
            $table->boolean('is_functional')->default(true)->after('sheet_status');
        });

        DB::table('material_inventory_items')
            ->where('sheet_status', MaterialSheetStatus::Guasto->value)
            ->update(['is_functional' => false]);
    }

    public function down(): void
    {
        Schema::table('material_inventory_items', function (Blueprint $table) {
            $table->dropColumn('is_functional');
        });
    }
};
