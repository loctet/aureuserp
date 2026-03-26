<?php

namespace Webkul\MaterialInventory\Filament\Resources\MaterialItemResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource;
use Webkul\MaterialInventory\Services\MaterialInventoryNumberIssuer;

class CreateMaterialItem extends CreateRecord
{
    protected static string $resource = MaterialItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] ??= Auth::user()?->default_company_id;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $reserved = MaterialInventoryNumberIssuer::reserveIdentifier(
            companyId: (int) $data['company_id'],
            category: $data['category'] ?? null,
            acquisitionDate: $data['acquisition_date'] ?? null,
        );

        $data['inventory_number'] = $reserved['inventory_number'];
        $data['progressive_asset_number'] = $reserved['progressive_asset_number'];
        $data['inventory_number_locked'] = true;

        return static::getModel()::create($data);
    }
}
