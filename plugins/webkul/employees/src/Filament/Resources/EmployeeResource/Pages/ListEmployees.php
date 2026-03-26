<?php

namespace Webkul\Employee\Filament\Resources\EmployeeResource\Pages;

use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Webkul\BulkImport\Filament\Actions\BulkCsvActions;
use Webkul\Employee\Filament\Resources\EmployeeResource;
use Webkul\Employee\Models\Employee;
use Webkul\TableViews\Filament\Components\PresetView;
use Webkul\TableViews\Filament\Concerns\HasTableViews;

class ListEmployees extends ListRecords
{
    use HasTableViews;

    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            BulkCsvActions::makeImportAction(
                Employee::class,
                [
                    'company_id'   => 'int',
                    'name'         => 'string',
                    'work_email'   => 'string',
                    'job_title'    => 'string',
                    'mobile_phone' => 'string',
                    'work_phone'   => 'string',
                    'is_active'    => 'bool',
                ],
            ),
            BulkCsvActions::makeTemplateAction(
                'employees-template.csv',
                ['company_id', 'name', 'work_email', 'job_title', 'mobile_phone', 'work_phone', 'is_active'],
                [
                    'company_id'   => 1,
                    'name'         => 'John Doe',
                    'work_email'   => 'john@example.com',
                    'job_title'    => 'Software Engineer',
                    'mobile_phone' => '+1555000111',
                    'work_phone'   => '+1555000222',
                    'is_active'    => 'true',
                ],
            ),
            CreateAction::make()
                ->icon('heroicon-o-plus-circle')
                ->label(__('employees::filament/resources/employee/pages/list-employee.header-actions.create.label')),
        ];
    }

    public function getPresetTableViews(): array
    {
        return [
            'my_team' => PresetView::make(__('employees::filament/resources/employee/pages/list-employee.tabs.my-team'))
                ->icon('heroicon-m-users')
                ->favorite()
                ->modifyQueryUsing(function (Builder $query) {
                    $user = Auth::user();

                    if (! $user->employee) {
                        return $query->whereNull('id');
                    }

                    return $query->where('parent_id', $user->employee->id);
                }),

            'my_department' => PresetView::make(__('employees::filament/resources/employee/pages/list-employee.tabs.my-department'))
                ->icon('heroicon-m-user-group')
                ->favorite()
                ->modifyQueryUsing(function (Builder $query) {
                    $user = Auth::user();

                    if (! $user->employee) {
                        return $query->whereNull('id');
                    }

                    return $query->where('department_id', $user->employee->department_id);
                }),

            'archived' => PresetView::make(__('employees::filament/resources/employee/pages/list-employee.tabs.archived'))
                ->icon('heroicon-s-archive-box')
                ->favorite()
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
            'newly_hired' => PresetView::make(__('employees::filament/resources/employee/pages/list-employee.tabs.newly-hired'))
                ->icon('heroicon-s-calendar')
                ->favorite()
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->where('created_at', '>=', Carbon::now()->subMonth());
                }),
        ];
    }
}
