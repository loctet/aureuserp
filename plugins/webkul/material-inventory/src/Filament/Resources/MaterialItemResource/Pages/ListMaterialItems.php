<?php

namespace Webkul\MaterialInventory\Filament\Resources\MaterialItemResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Webkul\MaterialInventory\Exports\InventoryMaterialeSheetExport;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource;

class ListMaterialItems extends ListRecords
{
    protected static string $resource = MaterialItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
