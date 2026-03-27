<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects_projects', function (Blueprint $table) {
            $table->string('lifecycle_stage')->default('proposal')->index()->after('budget');
            $table->string('cup_code')->nullable()->index()->after('lifecycle_stage');
            $table->string('grant_agreement_number')->nullable()->index()->after('cup_code');
            $table->string('funding_programme')->nullable()->after('grant_agreement_number');
            $table->decimal('co_financing_rate', 5, 2)->nullable()->after('funding_programme');

            $table->date('proposal_date')->nullable()->after('co_financing_rate');
            $table->date('evaluation_date')->nullable()->after('proposal_date');
            $table->date('negotiation_date')->nullable()->after('evaluation_date');
            $table->date('grant_agreement_date')->nullable()->after('negotiation_date');
            $table->date('active_date')->nullable()->after('grant_agreement_date');
            $table->date('final_review_date')->nullable()->after('active_date');
            $table->date('closed_date')->nullable()->after('final_review_date');
            $table->date('reporting_period_start')->nullable()->after('closed_date');
            $table->date('reporting_period_end')->nullable()->after('reporting_period_start');

            $table->decimal('budget_personnel_planned', 15, 2)->default(0)->after('reporting_period_end');
            $table->decimal('budget_personnel_spent', 15, 2)->default(0)->after('budget_personnel_planned');
            $table->decimal('budget_personnel_committed', 15, 2)->default(0)->after('budget_personnel_spent');

            $table->decimal('budget_subcontracting_planned', 15, 2)->default(0)->after('budget_personnel_committed');
            $table->decimal('budget_subcontracting_spent', 15, 2)->default(0)->after('budget_subcontracting_planned');
            $table->decimal('budget_subcontracting_committed', 15, 2)->default(0)->after('budget_subcontracting_spent');

            $table->decimal('budget_purchase_equipment_planned', 15, 2)->default(0)->after('budget_subcontracting_committed');
            $table->decimal('budget_purchase_equipment_spent', 15, 2)->default(0)->after('budget_purchase_equipment_planned');
            $table->decimal('budget_purchase_equipment_committed', 15, 2)->default(0)->after('budget_purchase_equipment_spent');

            $table->decimal('budget_purchase_other_planned', 15, 2)->default(0)->after('budget_purchase_equipment_committed');
            $table->decimal('budget_purchase_other_spent', 15, 2)->default(0)->after('budget_purchase_other_planned');
            $table->decimal('budget_purchase_other_committed', 15, 2)->default(0)->after('budget_purchase_other_spent');

            $table->decimal('budget_other_categories_planned', 15, 2)->default(0)->after('budget_purchase_other_committed');
            $table->decimal('budget_other_categories_spent', 15, 2)->default(0)->after('budget_other_categories_planned');
            $table->decimal('budget_other_categories_committed', 15, 2)->default(0)->after('budget_other_categories_spent');

            $table->decimal('budget_indirect_costs_planned', 15, 2)->default(0)->after('budget_other_categories_committed');
            $table->decimal('budget_indirect_costs_spent', 15, 2)->default(0)->after('budget_indirect_costs_planned');
            $table->decimal('budget_indirect_costs_committed', 15, 2)->default(0)->after('budget_indirect_costs_spent');
        });
    }

    public function down(): void
    {
        Schema::table('projects_projects', function (Blueprint $table) {
            $table->dropColumn([
                'lifecycle_stage',
                'cup_code',
                'grant_agreement_number',
                'funding_programme',
                'co_financing_rate',
                'proposal_date',
                'evaluation_date',
                'negotiation_date',
                'grant_agreement_date',
                'active_date',
                'final_review_date',
                'closed_date',
                'reporting_period_start',
                'reporting_period_end',
                'budget_personnel_planned',
                'budget_personnel_spent',
                'budget_personnel_committed',
                'budget_subcontracting_planned',
                'budget_subcontracting_spent',
                'budget_subcontracting_committed',
                'budget_purchase_equipment_planned',
                'budget_purchase_equipment_spent',
                'budget_purchase_equipment_committed',
                'budget_purchase_other_planned',
                'budget_purchase_other_spent',
                'budget_purchase_other_committed',
                'budget_other_categories_planned',
                'budget_other_categories_spent',
                'budget_other_categories_committed',
                'budget_indirect_costs_planned',
                'budget_indirect_costs_spent',
                'budget_indirect_costs_committed',
            ]);
        });
    }
};
