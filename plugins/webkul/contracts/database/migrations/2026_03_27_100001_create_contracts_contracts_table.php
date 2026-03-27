<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts_contracts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained('employees_employees')
                ->cascadeOnDelete();

            $table->foreignId('contract_type_id')
                ->nullable()
                ->constrained('contracts_contract_types')
                ->nullOnDelete();

            $table->date('start_date')->index();
            $table->date('end_date')->nullable()->index();
            $table->date('renewal_deadline')->nullable()->index();
            $table->string('status')->default('active')->index();
            $table->string('reference')->nullable()->index();
            $table->text('notes')->nullable();

            $table->foreignId('creator_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts_contracts');
    }
};
