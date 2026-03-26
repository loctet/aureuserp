<?php

namespace Webkul\MaterialInventory\Settings;

use Spatie\LaravelSettings\Settings;

class MaterialInventorySettings extends Settings
{
    public array $status_list = [
        ['value' => 'nuovo', 'label' => 'New'],
        ['value' => 'usato', 'label' => 'Used'],
        ['value' => 'guasto', 'label' => 'Broken'],
        ['value' => 'in_uso', 'label' => 'In use'],
        ['value' => 'in_riparazione', 'label' => 'Under repair'],
    ];

    public array $category_list = [
        'N-Notebook',
        'O-Asset Office',
        'L-Licenze SW ufficio',
        'S-Strumentazione HW',
    ];

    public string $default_storage_location = '';

    public bool $require_expected_return_date_on_checkout = false;

    public static function group(): string
    {
        return 'material_inventory';
    }
}
