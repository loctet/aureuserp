<?php

namespace Webkul\MaterialInventory\Filament\Resources\MaterialItemResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Webkul\BulkImport\Filament\Actions\BulkCsvActions;
use Webkul\MaterialInventory\Exports\InventoryMaterialeSheetExport;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource;
use Webkul\MaterialInventory\Models\MaterialItem;

class ListMaterialItems extends ListRecords
{
    protected static string $resource = MaterialItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            BulkCsvActions::makeImportAction(
                MaterialItem::class,
                [
                    'company_id'                    => 'int',
                    'name'                          => 'string',
                    'category'                      => 'string',
                    'sheet_status'                  => 'string',
                    'manufacturer'                  => 'string',
                    'model'                         => 'string',
                    'serial_number'                 => 'string',
                    'supplier'                      => 'string',
                    'acquisition_date'              => 'string',
                    'acquisition_cost'              => 'float',
                    'is_free'                       => 'bool',
                    'is_functional'                 => 'bool',
                    'storage_location'              => 'string',
                    'project_id'                    => 'int',
                    'current_custodian_employee_id' => 'int',
                    'expected_return_date'          => 'string',
                    'notes'                         => 'string',
                ],
            ),
            BulkCsvActions::makeTemplateAction(
                'material-items-template.csv',
                ['company_id', 'name', 'category', 'sheet_status', 'manufacturer', 'model', 'serial_number', 'supplier', 'acquisition_date', 'acquisition_cost', 'is_free', 'is_functional', 'storage_location', 'project_id', 'current_custodian_employee_id', 'expected_return_date', 'notes'],
                [
                    'company_id'                    => 1,
                    'name'                          => 'Dell Latitude 7440',
                    'category'                      => 'N-Notebook',
                    'sheet_status'                  => 'nuovo',
                    'manufacturer'                  => 'Dell',
                    'model'                         => 'Latitude 7440',
                    'serial_number'                 => 'SN-ABC-12345',
                    'supplier'                      => 'Aureus Supplier',
                    'acquisition_date'              => '2026-03-01',
                    'acquisition_cost'              => 1200.50,
                    'is_free'                       => 'false',
                    'is_functional'                 => 'true',
                    'storage_location'              => 'HQ - Rack A1',
                    'project_id'                    => 1,
                    'current_custodian_employee_id' => 1,
                    'expected_return_date'          => '2026-12-31',
                    'notes'                         => 'Assigned for development team usage',
                ],
            ),
            Action::make('exportExcel')
                ->label(__('material-inventory::filament/resources/material-item.table.export.label'))
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => Excel::download(
                    new InventoryMaterialeSheetExport(Auth::user()?->default_company_id),
                    'inventario-materiale.xlsx',
                )),
            CreateAction::make(),
        ];
    }
}
