<?php

namespace Webkul\MaterialInventory\Filament\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Webkul\Employee\Filament\Resources\EmployeeResource\Pages\CreateEmployee;
use Webkul\MaterialInventory\Enums\MaterialTransactionType;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource;
use Webkul\MaterialInventory\Models\MaterialInventoryTransaction;
use Webkul\PluginManager\Package;

class EmployeeMaterialTransactionsReceivedRelationManager extends RelationManager
{
    protected static string $relationship = 'materialInventoryTransactionsReceived';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('material-inventory::filament/resources/material-item.relation-managers.employee-received.title');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return Package::isPluginInstalled('material-inventory')
            && $pageClass !== CreateEmployee::class;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['item']))
            ->columns([
                TextColumn::make('occurred_at')->dateTime()->sortable(),
                TextColumn::make('item.inventory_number')->label('ID inventario'),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (?MaterialTransactionType $state) => $state?->name ?? ''),
                TextColumn::make('return_condition')->label('Return'),
                TextColumn::make('notes')->limit(30),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->recordActions([
                ViewAction::make('viewItem')
                    ->label(__('material-inventory::filament/resources/material-item.title'))
                    ->url(fn (MaterialInventoryTransaction $record): string => MaterialItemResource::getUrl('view', ['record' => $record->material_item_id]))
                    ->visible(fn (MaterialInventoryTransaction $record): bool => (bool) $record->material_item_id),
            ])
            ->headerActions([])
            ->toolbarActions([]);
    }
}
