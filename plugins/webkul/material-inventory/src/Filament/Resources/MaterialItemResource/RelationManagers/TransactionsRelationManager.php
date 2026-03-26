<?php

namespace Webkul\MaterialInventory\Filament\Resources\MaterialItemResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Webkul\MaterialInventory\Enums\MaterialTransactionType;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('material-inventory::filament/resources/material-item.relation-managers.transactions.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('occurred_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (?MaterialTransactionType $state) => $state?->name ?? ''),
                TextColumn::make('fromEmployee.name')
                    ->label('From'),
                TextColumn::make('toEmployee.name')
                    ->label('To'),
                TextColumn::make('return_condition')
                    ->label('Return state'),
                TextColumn::make('notes')
                    ->limit(40)
                    ->wrap(),
                TextColumn::make('performer.name')
                    ->label('By'),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->defaultSort('occurred_at', 'desc');
    }
}
