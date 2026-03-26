<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Webkul\MaterialInventory\Enums\MaterialSheetStatus;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('material_inventory.status_list', [
            [
                'value' => MaterialSheetStatus::Nuovo->value,
                'label' => MaterialSheetStatus::Nuovo->excelLabel(),
            ],
            [
                'value' => MaterialSheetStatus::Usato->value,
                'label' => MaterialSheetStatus::Usato->excelLabel(),
            ],
            [
                'value' => MaterialSheetStatus::Guasto->value,
                'label' => MaterialSheetStatus::Guasto->excelLabel(),
            ],
            [
                'value' => MaterialSheetStatus::InUso->value,
                'label' => MaterialSheetStatus::InUso->excelLabel(),
            ],
            [
                'value' => MaterialSheetStatus::InRiparazione->value,
                'label' => MaterialSheetStatus::InRiparazione->excelLabel(),
            ],
        ]);

        $this->migrator->add('material_inventory.category_list', [
            'N-Notebook',
            'O-Asset Office',
            'L-Licenze SW ufficio',
            'S-Strumentazione HW',
        ]);

        $this->migrator->add('material_inventory.default_storage_location', '');
        $this->migrator->add('material_inventory.require_expected_return_date_on_checkout', false);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('material_inventory.status_list');
        $this->migrator->deleteIfExists('material_inventory.category_list');
        $this->migrator->deleteIfExists('material_inventory.default_storage_location');
        $this->migrator->deleteIfExists('material_inventory.require_expected_return_date_on_checkout');
    }
};
