<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts_allocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained('employees_employees')
                ->cascadeOnDelete();

            $table->foreignId('project_id')
                ->constrained('projects_projects')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('work_package_id')
                ->nullable()
                ->index();

            $table->date('month')->index();
            $table->decimal('fte_percent', 5, 2)->default(0);
            $table->decimal('person_months', 8, 4)->default(0);
            $table->text('notes')->nullable();

            $table->foreignId('creator_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['employee_id', 'project_id', 'work_package_id', 'month'], 'contracts_allocations_month_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts_allocations');
    }
};
