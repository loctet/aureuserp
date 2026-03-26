<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->string('inventory_number')->index();
            $table->boolean('inventory_number_locked')->default(false);
            $table->unsignedInteger('progressive_asset_number')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable()->index();
            $table->string('serial_number')->nullable()->index();
            $table->string('model')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('supplier')->nullable();
            $table->date('acquisition_date')->nullable();

            $table->decimal('acquisition_cost', 15, 2)->nullable();
            $table->boolean('is_free')->default(false);

            $table->string('sheet_status')->default('nuovo')->index();

            $table->foreignId('project_id')
                ->nullable()
                ->constrained('projects_projects')
                ->nullOnDelete();

            $table->foreignId('current_custodian_employee_id')
                ->nullable()
                ->constrained('employees_employees')
                ->nullOnDelete();

            $table->timestamp('checked_out_at')->nullable();
            $table->date('assignment_date')->nullable();
            $table->date('expected_return_date')->nullable();
            $table->string('storage_location')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'inventory_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_inventory_items');
    }
};
