<?php

namespace Webkul\MaterialInventory\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Webkul\MaterialInventory\Models\MaterialItem;
use Webkul\MaterialInventory\Support\MaterialInventoryOptions;

class InventoryMaterialeSheetExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStrictNullComparison, WithTitle
{
    public function __construct(
        protected ?int $companyId = null,
    ) {}

    public function collection(): Collection
    {
        $q = MaterialItem::query()
            ->with(['currentCustodian'])
            ->orderBy('progressive_asset_number')
            ->orderBy('inventory_number');

        if ($this->companyId !== null) {
            $q->where('company_id', $this->companyId);
        }

        return $q->get();
    }

    public function title(): string
    {
        return 'Inventory';
    }

    public function headings(): array
    {
        return [
            'Inventory ID',
            'Asset number (progressive)',
            'Category',
            'Acquisition date',
            'Asset description',
            'Brand',
            'Model',
            'Serial number',
            'Status (New/Used/Broken/In use)',
            'Supplier',
            'Location',
            'Current assignee',
            'Assignment date',
        ];
    }

    /**
     * @param  MaterialItem  $row
     */
    public function map($row): array
    {
        $status = MaterialInventoryOptions::humanizeStatus((string) $row->sheet_status);

        return [
            $row->inventory_number,
            $row->progressive_asset_number,
            $row->category,
            $row->acquisition_date?->format('d/m/Y'),
            $row->name,
            $row->manufacturer,
            $row->model,
            $row->serial_number,
            $status,
            $row->supplier,
            $row->storage_location,
            $row->currentCustodian?->name,
            $row->assignment_date?->format('d/m/Y'),
        ];
    }
}
