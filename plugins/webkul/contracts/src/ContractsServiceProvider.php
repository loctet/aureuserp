<?php

namespace Webkul\Contracts;

use Filament\Panel;
use Webkul\Contracts\Models\Allocation;
use Webkul\Contracts\Models\Contract;
use Webkul\Contracts\Services\ContractExpiryAlertService;
use Webkul\Employee\Models\Employee;
use Webkul\PluginManager\Console\Commands\InstallCommand;
use Webkul\PluginManager\Console\Commands\UninstallCommand;
use Webkul\PluginManager\Package;
use Webkul\PluginManager\PackageServiceProvider;
use Webkul\Project\Models\Project;
use Webkul\Project\Models\WorkPackage;

class ContractsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'contracts';

    public function configureCustomPackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasRoute('api')
            ->hasTranslations()
            ->hasDependencies([
                'employees',
                'projects',
            ])
            ->hasMigrations([
                '2026_03_27_100000_create_contracts_contract_types_table',
                '2026_03_27_100001_create_contracts_contracts_table',
                '2026_03_27_100002_create_contracts_hourly_cost_certifications_table',
                '2026_03_27_100003_create_contracts_allocations_table',
                '2026_03_27_100500_add_work_package_fk_to_contracts_allocations_table',
            ])
            ->runsMigrations()
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->installDependencies()
                    ->runsMigrations();
            })
            ->hasUninstallCommand(function (UninstallCommand $command) {})
            ->icon('contracts');
    }

    public function packageBooted(): void
    {
        app(ContractExpiryAlertService::class)->registerModelEvents();

        if (class_exists(Employee::class)) {
            Employee::resolveRelationUsing('contracts', function (Employee $employee) {
                return $employee->hasMany(Contract::class, 'employee_id');
            });

            Employee::resolveRelationUsing('workforceAllocations', function (Employee $employee) {
                return $employee->hasMany(Allocation::class, 'employee_id');
            });

        }

        if (class_exists(Project::class)) {
            Project::resolveRelationUsing('workforceAllocations', function (Project $project) {
                return $project->hasMany(Allocation::class, 'project_id');
            });
        }

        if (class_exists(WorkPackage::class)) {
            WorkPackage::resolveRelationUsing('workforceAllocations', function (WorkPackage $workPackage) {
                return $workPackage->hasMany(Allocation::class, 'work_package_id');
            });
        }
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            $panel->plugin(ContractsPlugin::make());
        });
    }

}
