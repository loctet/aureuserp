<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees_monthly_availability', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained('employees_employees')
                ->cascadeOnDelete();

            $table->date('month')->index();
            $table->decimal('fte_percent', 5, 2)->default(100);
            $table->decimal('person_months', 8, 4)->default(1);
            $table->text('notes')->nullable();

            $table->foreignId('creator_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['employee_id', 'month'], 'employees_monthly_availability_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees_monthly_availability');
    }
};
