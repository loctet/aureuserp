<?php

namespace Webkul\MaterialInventory\Filament\Resources\MaterialItemResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Webkul\Employee\Models\Employee;
use Webkul\MaterialInventory\Enums\MaterialSheetStatus;
use Webkul\MaterialInventory\Enums\MaterialTransactionType;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource;
use Webkul\MaterialInventory\Models\MaterialItem;
use Webkul\MaterialInventory\Services\MaterialInventoryNumberIssuer;
use Webkul\MaterialInventory\Services\MaterialInventoryTransactionRecorder;
use Webkul\Project\Models\Project;

class ViewMaterialItem extends ViewRecord
{
    protected static string $resource = MaterialItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('issueFormalId')
                ->label(__('material-inventory::filament/resources/material-item.actions.issue_formal_id.label'))
                ->icon('heroicon-o-identification')
                ->visible(fn (MaterialItem $record): bool => ! $record->inventory_number_locked)
                ->requiresConfirmation()
                ->modalDescription(__('material-inventory::filament/resources/material-item.actions.issue_formal_id.body'))
                ->action(function (MaterialItem $record): void {
                    if ($record->inventory_number_locked) {
                        return;
                    }

                    MaterialInventoryNumberIssuer::issueFormalId($record);
                    $record->refresh();

                    MaterialInventoryTransactionRecorder::record($record, MaterialTransactionType::Register, [
                        'notes' => __('material-inventory::filament/resources/material-item.actions.issue_formal_id.label'),
                    ]);

                    Notification::make()
                        ->success()
                        ->title(__('material-inventory::filament/resources/material-item.notifications.issue_ok'))
                        ->send();

                    $this->record = $record->fresh();
                }),
            Action::make('checkOut')
                ->label(__('material-inventory::filament/resources/material-item.actions.check_out.label'))
                ->icon('heroicon-o-arrow-right-circle')
                ->visible(fn (MaterialItem $record): bool => ! $record->current_custodian_employee_id && $record->sheet_status !== MaterialSheetStatus::InRiparazione)
                ->schema([
                    Select::make('employee_id')
                        ->label(__('material-inventory::filament/resources/material-item.table.columns.custodian'))
                        ->options(fn () => Employee::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    DatePicker::make('assignment_date')
                        ->label(__('material-inventory::filament/resources/material-item.form.sections.custody.fields.assignment_date'))
                        ->default(now())
                        ->native(false)
                        ->required(),
                ])
                ->action(function (MaterialItem $record, array $data): void {
                    if ($record->current_custodian_employee_id) {
                        Notification::make()->danger()->title(__('material-inventory::filament/resources/material-item.notifications.already_assigned'))->send();

                        return;
                    }

                    if ($record->sheet_status === MaterialSheetStatus::InRiparazione) {
                        Notification::make()->danger()->title(__('material-inventory::filament/resources/material-item.notifications.under_repair'))->send();

                        return;
                    }

                    if (! $record->isFunctional()) {
                        Notification::make()->danger()->title(__('material-inventory::filament/resources/material-item.notifications.non_functional'))->send();

                        return;
                    }

                    $from = $record->current_custodian_employee_id;
                    $before = $record->sheet_status?->value;

                    $record->update([
                        'current_custodian_employee_id' => $data['employee_id'],
                        'assignment_date'               => $data['assignment_date'],
                        'sheet_status'                  => MaterialSheetStatus::InUso,
                        'checked_out_at'                => now(),
                    ]);

                    MaterialInventoryTransactionRecorder::record($record->fresh(), MaterialTransactionType::CheckOut, [
                        'from_employee_id'   => $from,
                        'to_employee_id'     => $data['employee_id'],
                        'condition_before'   => $before,
                        'condition_after'    => MaterialSheetStatus::InUso->value,
                    ]);

                    Notification::make()->success()->title(__('material-inventory::filament/resources/material-item.actions.check_out.label'))->send();
                    $this->record = $record->fresh();
                }),
            Action::make('checkIn')
                ->label(__('material-inventory::filament/resources/material-item.actions.check_in.label'))
                ->icon('heroicon-o-arrow-left-circle')
                ->visible(fn (MaterialItem $record): bool => $record->current_custodian_employee_id !== null)
                ->schema([
                    Select::make('return_condition')
                        ->label(__('material-inventory::filament/resources/material-item.form.sections.asset.fields.sheet_status'))
                        ->options(collect(MaterialItemResource::materialStatusOptions())
                            ->only([
                                MaterialSheetStatus::Nuovo->value,
                                MaterialSheetStatus::Usato->value,
                                MaterialSheetStatus::Guasto->value,
                            ])
                            ->all())
                        ->default(MaterialSheetStatus::Usato->value)
                        ->required(),
                    DatePicker::make('return_date')
                        ->label(__('material-inventory::filament/resources/material-item.actions.check_in.return_date'))
                        ->default(now())
                        ->native(false)
                        ->required(),
                    Textarea::make('notes')->label(__('material-inventory::filament/resources/material-item.form.sections.custody.fields.notes')),
                ])
                ->action(function (MaterialItem $record, array $data): void {
                    if (! $record->current_custodian_employee_id) {
                        Notification::make()->danger()->title(__('material-inventory::filament/resources/material-item.notifications.not_assigned'))->send();

                        return;
                    }

                    $from = $record->current_custodian_employee_id;
                    $before = $record->sheet_status?->value;
                    $return = MaterialSheetStatus::from($data['return_condition']);

                    $record->update([
                        'current_custodian_employee_id' => null,
                        'assignment_date'               => null,
                        'expected_return_date'          => null,
                        'sheet_status'                  => $return,
                        'checked_out_at'                => null,
                    ]);

                    MaterialInventoryTransactionRecorder::record($record->fresh(), MaterialTransactionType::CheckIn, [
                        'from_employee_id'   => $from,
                        'condition_before'   => $before,
                        'condition_after'    => $return->value,
                        'return_condition'   => $return->value,
                        'notes'              => $data['notes'] ?? null,
                        'meta'               => [
                            'return_date' => $data['return_date'] ?? now()->toDateString(),
                        ],
                    ]);

                    Notification::make()->success()->title(__('material-inventory::filament/resources/material-item.actions.check_in.label'))->send();
                    $this->record = $record->fresh();
                }),
            Action::make('sendRepair')
                ->label(__('material-inventory::filament/resources/material-item.actions.send_repair.label'))
                ->icon('heroicon-o-wrench-screwdriver')
                ->visible(fn (MaterialItem $record): bool => $record->sheet_status !== MaterialSheetStatus::InRiparazione)
                ->schema([
                    Textarea::make('notes')->required(),
                ])
                ->action(function (MaterialItem $record, array $data): void {
                    if ($record->current_custodian_employee_id) {
                        Notification::make()->danger()->title(__('material-inventory::filament/resources/material-item.notifications.repair_requires_return'))->send();

                        return;
                    }

                    $before = $record->sheet_status?->value;

                    $record->update([
                        'sheet_status' => MaterialSheetStatus::InRiparazione,
                    ]);

                    MaterialInventoryTransactionRecorder::record($record->fresh(), MaterialTransactionType::SendRepair, [
                        'condition_before' => $before,
                        'condition_after'  => MaterialSheetStatus::InRiparazione->value,
                        'notes'            => $data['notes'],
                    ]);

                    Notification::make()->success()->send();
                    $this->record = $record->fresh();
                }),
            Action::make('returnRepair')
                ->label(__('material-inventory::filament/resources/material-item.actions.return_repair.label'))
                ->icon('heroicon-o-check-badge')
                ->visible(fn (MaterialItem $record): bool => $record->sheet_status === MaterialSheetStatus::InRiparazione)
                ->schema([
                    Select::make('sheet_status')
                        ->label(__('material-inventory::filament/resources/material-item.form.sections.asset.fields.sheet_status'))
                        ->options(collect(MaterialItemResource::materialStatusOptions())
                            ->only([
                                MaterialSheetStatus::Usato->value,
                                MaterialSheetStatus::Nuovo->value,
                                MaterialSheetStatus::Guasto->value,
                            ])
                            ->all())
                        ->default(MaterialSheetStatus::Usato->value)
                        ->required(),
                    DatePicker::make('repair_end_date')
                        ->label(__('material-inventory::filament/resources/material-item.actions.repair.end_date'))
                        ->native(false)
                        ->required(),
                    TextInput::make('repair_cost')
                        ->label(__('material-inventory::filament/resources/material-item.actions.repair.cost'))
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),
                    Select::make('repair_cost_assignment')
                        ->label(__('material-inventory::filament/resources/material-item.actions.repair.assignment'))
                        ->options([
                            'internal' => __('material-inventory::filament/resources/material-item.actions.repair.assignment_internal'),
                            'project'  => __('material-inventory::filament/resources/material-item.actions.repair.assignment_project'),
                        ])
                        ->default('internal')
                        ->required(),
                    Select::make('repair_project_id')
                        ->label(__('material-inventory::filament/resources/material-item.actions.repair.project'))
                        ->options(fn () => Project::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable(),
                    Select::make('is_functional_after_repair')
                        ->label(__('material-inventory::filament/resources/material-item.actions.repair.functional_after'))
                        ->options([
                            1 => 'Yes',
                            0 => 'No',
                        ])
                        ->default(1)
                        ->required(),
                    Textarea::make('notes'),
                ])
                ->action(function (MaterialItem $record, array $data): void {
                    if ($record->sheet_status !== MaterialSheetStatus::InRiparazione) {
                        Notification::make()->danger()->title(__('material-inventory::filament/resources/material-item.notifications.under_repair'))->send();

                        return;
                    }

                    if (($data['repair_cost_assignment'] ?? 'internal') === 'project' && blank($data['repair_project_id'] ?? null)) {
                        Notification::make()->danger()->title(__('material-inventory::filament/resources/material-item.actions.repair.project').' is required.')->send();

                        return;
                    }

                    $before = $record->sheet_status?->value;
                    $after = MaterialSheetStatus::from($data['sheet_status']);
                    $isFunctional = (bool) ($data['is_functional_after_repair'] ?? true);

                    if (! $isFunctional) {
                        $after = MaterialSheetStatus::Guasto;
                    }

                    $updateData = [
                        'sheet_status' => $after,
                    ];

                    if (MaterialItem::hasFunctionalColumn()) {
                        $updateData['is_functional'] = $isFunctional;
                    }

                    $record->update($updateData);

                    MaterialInventoryTransactionRecorder::record($record->fresh(), MaterialTransactionType::ReturnFromRepair, [
                        'condition_before' => $before,
                        'condition_after'  => $after->value,
                        'notes'            => $data['notes'] ?? null,
                        'meta'             => [
                            'repair_end_date'        => $data['repair_end_date'] ?? null,
                            'repair_cost'            => (float) ($data['repair_cost'] ?? 0),
                            'repair_cost_assignment' => $data['repair_cost_assignment'] ?? 'internal',
                            'repair_project_id'      => $data['repair_project_id'] ?? null,
                            'functional_after'       => $isFunctional,
                        ],
                    ]);

                    Notification::make()->success()->send();
                    $this->record = $record->fresh();
                }),
            EditAction::make(),
        ];
    }
}
