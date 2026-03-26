<?php

namespace Webkul\MaterialInventory\Filament\Resources\MaterialItemResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Webkul\Employee\Models\Employee;
use Webkul\MaterialInventory\Enums\MaterialSheetStatus;
use Webkul\MaterialInventory\Enums\MaterialTransactionType;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource;
use Webkul\MaterialInventory\Models\MaterialItem;
use Webkul\MaterialInventory\Services\MaterialInventoryNumberIssuer;
use Webkul\MaterialInventory\Services\MaterialInventoryTransactionRecorder;

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
                        ->options([
                            MaterialSheetStatus::Nuovo->value   => MaterialSheetStatus::Nuovo->excelLabel(),
                            MaterialSheetStatus::Usato->value   => MaterialSheetStatus::Usato->excelLabel(),
                            MaterialSheetStatus::Guasto->value  => MaterialSheetStatus::Guasto->excelLabel(),
                        ])
                        ->default(MaterialSheetStatus::Usato->value)
                        ->required(),
                    Textarea::make('notes')->label(__('material-inventory::filament/resources/material-item.form.sections.custody.fields.notes')),
                ])
                ->action(function (MaterialItem $record, array $data): void {
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
                    ]);

                    Notification::make()->success()->title(__('material-inventory::filament/resources/material-item.actions.check_in.label'))->send();
                    $this->record = $record->fresh();
                }),
            Action::make('sendRepair')
                ->label(__('material-inventory::filament/resources/material-item.actions.send_repair.label'))
                ->icon('heroicon-o-wrench-screwdriver')
                ->schema([
                    Textarea::make('notes')->required(),
                ])
                ->action(function (MaterialItem $record, array $data): void {
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
                        ->options([
                            MaterialSheetStatus::Usato->value   => MaterialSheetStatus::Usato->excelLabel(),
                            MaterialSheetStatus::Nuovo->value   => MaterialSheetStatus::Nuovo->excelLabel(),
                            MaterialSheetStatus::Guasto->value  => MaterialSheetStatus::Guasto->excelLabel(),
                        ])
                        ->default(MaterialSheetStatus::Usato->value)
                        ->required(),
                    Textarea::make('notes'),
                ])
                ->action(function (MaterialItem $record, array $data): void {
                    $before = $record->sheet_status?->value;
                    $after = MaterialSheetStatus::from($data['sheet_status']);

                    $record->update(['sheet_status' => $after]);

                    MaterialInventoryTransactionRecorder::record($record->fresh(), MaterialTransactionType::ReturnFromRepair, [
                        'condition_before' => $before,
                        'condition_after'  => $after->value,
                        'notes'            => $data['notes'] ?? null,
                    ]);

                    Notification::make()->success()->send();
                    $this->record = $record->fresh();
                }),
            EditAction::make(),
        ];
    }
}
