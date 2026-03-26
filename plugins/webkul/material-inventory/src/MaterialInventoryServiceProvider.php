<?php

namespace Webkul\MaterialInventory;

use Filament\Panel;
use Illuminate\Validation\ValidationException;
use Webkul\Employee\Models\Employee;
use Webkul\MaterialInventory\Models\MaterialInventoryTransaction;
use Webkul\MaterialInventory\Models\MaterialItem;
use Webkul\MaterialInventory\Services\MaterialProjectBudgetGuard;
use Webkul\PluginManager\Console\Commands\InstallCommand;
use Webkul\PluginManager\Console\Commands\UninstallCommand;
use Webkul\PluginManager\Package;
use Webkul\PluginManager\PackageServiceProvider;
use Webkul\Project\Models\Project;

class MaterialInventoryServiceProvider extends PackageServiceProvider
{
    public static string $name = 'material-inventory';

    public function register()
    {
        parent::register();

        // Register before Filament boot so namespaced keys are never first resolved with an empty group cache.
        $this->loadTranslationsFrom(
            dirname(__DIR__).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'lang',
            static::$name
        );

        return $this;
    }

    public function configureCustomPackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasDependencies('projects', 'employees')
            ->hasTranslations()
            ->hasMigrations([
                '2026_03_25_140000_create_material_inventory_items_table',
                '2026_03_25_140001_create_material_inventory_transactions_table',
                '2026_03_26_100000_align_material_inventory_with_excel_sheet',
                '2026_03_26_100001_add_return_fields_to_material_inventory_transactions',
            ])
            ->runsMigrations()
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->runsMigrations();
            })
            ->hasUninstallCommand(function (UninstallCommand $command) {})
            ->icon('inventories');
    }

    public function packageBooted(): void
    {
        if (class_exists(Employee::class)) {
            Employee::resolveRelationUsing('materialInventoryItems', function (Employee $employee) {
                return $employee->hasMany(MaterialItem::class, 'current_custodian_employee_id');
            });

            Employee::resolveRelationUsing('materialInventoryTransactionsReceived', function (Employee $employee) {
                return $employee->hasMany(MaterialInventoryTransaction::class, 'to_employee_id');
            });

            Employee::resolveRelationUsing('materialInventoryTransactionsSent', function (Employee $employee) {
                return $employee->hasMany(MaterialInventoryTransaction::class, 'from_employee_id');
            });
        }

        if (class_exists(Project::class)) {
            Project::resolveRelationUsing('materialInventoryItems', function (Project $project) {
                return $project->hasMany(MaterialItem::class, 'project_id');
            });
        }

        MaterialItem::saving(function (MaterialItem $item): void {
            if (! $item->project_id) {
                return;
            }

            $project = Project::find($item->project_id);

            if (! $project) {
                return;
            }

            if (! MaterialProjectBudgetGuard::canAssignItemToProject($project, $item)) {
                throw ValidationException::withMessages([
                    'project_id' => __('material-inventory::filament/resources/material-item.notifications.budget'),
                ]);
            }
        });
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            $panel->plugin(MaterialInventoryPlugin::make());
        });
    }
}
