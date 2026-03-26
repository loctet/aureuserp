<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('material_inventory_general.categories', [
            'N-Notebook',
            'O-Asset Office',
            'L-Software License',
            'S-Hardware Instrument',
        ]);

        $this->migrator->add('material_inventory_general.statuses', [
            'new',
            'used',
            'broken',
            'in_use',
            'under_repair',
        ]);

        $this->migrator->add('material_inventory_general.default_status', 'new');
        $this->migrator->add('material_inventory_general.status_in_use', 'in_use');
        $this->migrator->add('material_inventory_general.status_under_repair', 'under_repair');
        $this->migrator->add('material_inventory_general.enforce_project_budget', true);
        $this->migrator->add('material_inventory_general.default_expected_return_days', 0);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('material_inventory_general.categories');
        $this->migrator->deleteIfExists('material_inventory_general.statuses');
        $this->migrator->deleteIfExists('material_inventory_general.default_status');
        $this->migrator->deleteIfExists('material_inventory_general.status_in_use');
        $this->migrator->deleteIfExists('material_inventory_general.status_under_repair');
        $this->migrator->deleteIfExists('material_inventory_general.enforce_project_budget');
        $this->migrator->deleteIfExists('material_inventory_general.default_expected_return_days');
    }
};
