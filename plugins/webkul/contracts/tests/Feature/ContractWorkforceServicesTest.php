<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Webkul\Contracts\Models\Allocation;
use Webkul\Contracts\Models\Contract;
use Webkul\Contracts\Models\ContractType;
use Webkul\Contracts\Models\HourlyCostCertification;
use Webkul\Contracts\Notifications\ContractExpiryAlertNotification;
use Webkul\Contracts\Services\AvailabilityBalanceService;
use Webkul\Contracts\Services\ContractExpiryAlertService;
use Webkul\Contracts\Services\PersonnelCostService;
use Webkul\Employee\Models\Employee;
use Webkul\Employee\Models\EmployeeMonthlyAvailability;
use Webkul\Project\Models\Project;
use Webkul\Project\Models\WorkPackage;
use Webkul\Security\Models\User;
use Webkul\Support\Models\Currency;

require_once __DIR__.'/../../../support/tests/Helpers/TestBootstrapHelper.php';

beforeEach(function () {
    TestBootstrapHelper::ensureERPInstalled();

    Artisan::call('employees:install', ['--no-interaction' => true]);
    Artisan::call('projects:install', ['--no-interaction' => true]);
    Artisan::call('contracts:install', ['--no-interaction' => true]);
});

it('calculates remaining monthly availability from allocations', function () {
    $employee = Employee::factory()->create();
    $project = Project::factory()->create();
    $workPackage = WorkPackage::query()->create([
        'project_id' => $project->id,
        'name'       => 'WP-1',
        'code'       => 'WP1',
        'is_active'  => true,
    ]);

    EmployeeMonthlyAvailability::query()->create([
        'employee_id'   => $employee->id,
        'month'         => '2026-03-01',
        'fte_percent'   => 100,
        'person_months' => 1.0,
    ]);

    Allocation::query()->create([
        'employee_id'    => $employee->id,
        'project_id'     => $project->id,
        'work_package_id'=> $workPackage->id,
        'month'          => '2026-03-01',
        'fte_percent'    => 40,
        'person_months'  => 0.4,
    ]);

    $service = app(AvailabilityBalanceService::class);

    expect($service->remainingFtePercent($employee->id, '2026-03-01'))->toBe(60.0);
    expect($service->remainingPersonMonths($employee->id, '2026-03-01'))->toBe(0.6);
});

it('calculates personnel cost from certified hourly rate', function () {
    $employee = Employee::factory()->create();
    $currency = Currency::query()->firstOrFail();
    $contractType = ContractType::query()->create(['name' => 'Research']);
    $contract = Contract::query()->create([
        'employee_id'       => $employee->id,
        'contract_type_id'  => $contractType->id,
        'start_date'        => '2026-01-01',
        'end_date'          => '2026-12-31',
        'renewal_deadline'  => '2026-10-01',
        'status'            => 'active',
    ]);

    HourlyCostCertification::query()->create([
        'contract_id'           => $contract->id,
        'currency_id'           => $currency->id,
        'certified_hourly_cost' => 55.50,
        'effective_from'        => '2026-01-01',
        'is_active'             => true,
    ]);

    $service = app(PersonnelCostService::class);
    $cost = $service->calculatePersonnelCost($contract, 100, '2026-03-15');

    expect($cost)->toBe(5550.0);
});

it('sends expiry alert notifications at 30 days', function () {
    Notification::fake();

    $employee = Employee::factory()->create();
    $user = User::query()->findOrFail($employee->user_id);
    $contractType = ContractType::query()->create(['name' => 'Fixed term']);
    $contract = Contract::query()->create([
        'employee_id'      => $employee->id,
        'contract_type_id' => $contractType->id,
        'start_date'       => now()->subMonths(2)->toDateString(),
        'end_date'         => now()->addDays(30)->toDateString(),
        'status'           => 'active',
        'creator_id'       => $user->id,
    ]);

    app(ContractExpiryAlertService::class)->sendAlertsForContract($contract);

    Notification::assertSentTo([$user], ContractExpiryAlertNotification::class);
});
