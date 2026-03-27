<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees_skills', function (Blueprint $table) {
            $table->foreignId('skill_discipline_id')
                ->nullable()
                ->after('skill_type_id')
                ->constrained('employees_skill_disciplines')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employees_skills', function (Blueprint $table) {
            $table->dropConstrainedForeignId('skill_discipline_id');
        });
    }
};
