<?php

namespace Webkul\MaterialInventory\Filament\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Webkul\Employee\Filament\Resources\EmployeeResource\Pages\CreateEmployee;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource;
use Webkul\MaterialInventory\Services\MaterialInventoryNumberIssuer;
use Webkul\PluginManager\Package;

class EmployeeMaterialItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'materialInventoryItems';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('material-inventory::filament/resources/material-item.relation-managers.employee-items.title');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return Package::isPluginInstalled('material-inventory')
            && $pageClass !== CreateEmployee::class;
    }

    public function form(Schema $schema): Schema
    {
        return MaterialItemResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return MaterialItemResource::table($table)
            ->filters([])
            ->groups([])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['inventory_number'] = MaterialInventoryNumberIssuer::draftInventoryNumber();
                        $data['current_custodian_employee_id'] = $this->getOwnerRecord()->getKey();
                        $data['company_id'] ??= $this->getOwnerRecord()->company_id;

                        return $data;
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('material-inventory::filament/resources/material-item.title')),
                    ),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => MaterialItemResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
