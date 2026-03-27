<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts_allocations', function (Blueprint $table) {
            $table->foreign('work_package_id')
                ->references('id')
                ->on('projects_work_packages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contracts_allocations', function (Blueprint $table) {
            $table->dropForeign(['work_package_id']);
        });
    }
};
