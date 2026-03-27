<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('activity_plans')) {
            return;
        }

        Schema::table('activity_plans', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained('employees_departments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('activity_plans')) {
            return;
        }

        Schema::table('activity_plans', function (Blueprint $table) {
            if (Schema::hasColumn('activity_plans', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }
        });
    }
};
