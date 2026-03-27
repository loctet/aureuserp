<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts_hourly_cost_certifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contract_id')
                ->constrained('contracts_contracts')
                ->cascadeOnDelete();

            $table->foreignId('currency_id')
                ->nullable()
                ->constrained('currencies')
                ->nullOnDelete();

            $table->decimal('certified_hourly_cost', 15, 2);
            $table->date('effective_from')->index();
            $table->date('effective_to')->nullable()->index();
            $table->string('certificate_reference')->nullable()->index();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();

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
        Schema::dropIfExists('contracts_hourly_cost_certifications');
    }
};
