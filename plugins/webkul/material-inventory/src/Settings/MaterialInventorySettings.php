<?php

namespace Webkul\MaterialInventory\Settings;

use Spatie\LaravelSettings\Settings;

class MaterialInventorySettings extends Settings
{
    /** @var array<int, string> */
    public array $categories;

    /** @var array<int, string> */
    public array $statuses;

    public string $default_status;

    public string $status_in_use;

    public string $status_under_repair;

    public bool $enforce_project_budget;

    public int $default_expected_return_days;

    public static function group(): string
    {
        return 'material_inventory_general';
    }
}
