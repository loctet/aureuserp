<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_inventory_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('material_item_id')
                ->constrained('material_inventory_items')
                ->cascadeOnDelete();

            $table->string('type')->index();

            $table->foreignId('from_employee_id')
                ->nullable()
                ->constrained('employees_employees')
                ->nullOnDelete();

            $table->foreignId('to_employee_id')
                ->nullable()
                ->constrained('employees_employees')
                ->nullOnDelete();

            $table->foreignId('from_project_id')
                ->nullable()
                ->constrained('projects_projects')
                ->nullOnDelete();

            $table->foreignId('to_project_id')
                ->nullable()
                ->constrained('projects_projects')
                ->nullOnDelete();

            $table->string('condition_before')->nullable();
            $table->string('condition_after')->nullable();

            $table->text('notes')->nullable();
            $table->json('meta')->nullable();

            $table->foreignId('performed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('occurred_at')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_inventory_transactions');
    }
};
