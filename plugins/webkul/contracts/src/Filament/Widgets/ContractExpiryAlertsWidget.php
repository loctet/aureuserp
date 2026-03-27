<?php

namespace Webkul\Contracts\Filament\Widgets;

use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Webkul\Contracts\Models\Contract;

class ContractExpiryAlertsWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Contract::query()
                    ->whereNotNull('end_date')
                    ->whereDate('end_date', '>=', now()->toDateString())
                    ->whereDate('end_date', '<=', now()->addDays(90)->toDateString())
                    ->orderBy('end_date')
            )
            ->columns([
                TextColumn::make('reference')
                    ->label('Reference')
                    ->sortable(),
                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->sortable(),
                TextColumn::make('contractType.name')
                    ->label('Type')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('End date')
                    ->date()
                    ->sortable(),
                TextColumn::make('days_to_expiry')
                    ->label('Days left')
                    ->state(fn (Contract $record): int => now()->diffInDays($record->end_date, false)),
            ]);
    }
}
