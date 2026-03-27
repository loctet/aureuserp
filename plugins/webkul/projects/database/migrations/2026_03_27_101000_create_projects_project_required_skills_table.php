<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects_project_required_skills', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects_projects')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('skill_domain_id')->nullable()->index();
            $table->unsignedBigInteger('skill_discipline_id')->nullable()->index();
            $table->unsignedBigInteger('skill_id')->nullable()->index();

            $table->string('proficiency')->default('basic')->index();
            $table->decimal('required_fte_percent', 5, 2)->default(0);
            $table->decimal('required_person_months', 8, 4)->default(0);
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
        Schema::dropIfExists('projects_project_required_skills');
    }
};
