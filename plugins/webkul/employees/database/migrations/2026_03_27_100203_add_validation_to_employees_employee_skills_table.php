<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees_employee_skills', function (Blueprint $table) {
            $table->string('proficiency')
                ->default('basic')
                ->after('skill_level_id');

            $table->string('validation_status')
                ->default('pending')
                ->after('proficiency');

            $table->foreignId('validated_by')
                ->nullable()
                ->after('validation_status')
                ->constrained('employees_employees')
                ->nullOnDelete();

            $table->timestamp('validated_at')
                ->nullable()
                ->after('validated_by');

            $table->text('validation_notes')
                ->nullable()
                ->after('validated_at');
        });
    }

    public function down(): void
    {
        Schema::table('employees_employee_skills', function (Blueprint $table) {
            $table->dropConstrainedForeignId('validated_by');
            $table->dropColumn(['proficiency', 'validation_status', 'validated_at', 'validation_notes']);
        });
    }
};
