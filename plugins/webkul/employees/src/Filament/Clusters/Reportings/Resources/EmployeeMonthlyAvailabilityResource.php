<?php

namespace Webkul\Employee\Filament\Clusters\Reportings\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Webkul\Employee\Filament\Clusters\Reportings;
use Webkul\Employee\Filament\Clusters\Reportings\Resources\EmployeeMonthlyAvailabilityResource\Pages\ManageEmployeeMonthlyAvailabilities;
use Webkul\Employee\Models\EmployeeMonthlyAvailability;

class EmployeeMonthlyAvailabilityResource extends Resource
{
    protected static ?string $model = EmployeeMonthlyAvailability::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $cluster = Reportings::class;

    public static function getNavigationLabel(): string
    {
        return 'Availability';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('month')
                    ->native(false)
                    ->required(),
                TextInput::make('fte_percent')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required(),
                TextInput::make('person_months')
                    ->numeric()
                    ->minValue(0)
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('month')
                    ->date()
                    ->sortable(),
                TextColumn::make('fte_percent')
                    ->numeric(2)
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('person_months')
                    ->numeric(4)
                    ->sortable(),
                TextColumn::make('remaining_fte_percent')
                    ->label('Remaining FTE')
                    ->state(function (EmployeeMonthlyAvailability $record): float {
                        if (! SchemaFacade::hasTable('contracts_allocations')) {
                            return (float) $record->fte_percent;
                        }

                        $allocated = (float) DB::table('contracts_allocations')
                            ->where('employee_id', $record->employee_id)
                            ->whereDate('month', $record->month)
                            ->whereNull('deleted_at')
                            ->sum('fte_percent');

                        return max(0, round((float) $record->fte_percent - $allocated, 2));
                    })
                    ->numeric(2)
                    ->suffix('%'),
                TextColumn::make('remaining_person_months')
                    ->label('Remaining PM')
                    ->state(function (EmployeeMonthlyAvailability $record): float {
                        if (! SchemaFacade::hasTable('contracts_allocations')) {
                            return (float) $record->person_months;
                        }

                        $allocated = (float) DB::table('contracts_allocations')
                            ->where('employee_id', $record->employee_id)
                            ->whereDate('month', $record->month)
                            ->whereNull('deleted_at')
                            ->sum('person_months');

                        return max(0, round((float) $record->person_months - $allocated, 4));
                    })
                    ->numeric(4),
                TextColumn::make('creator.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageEmployeeMonthlyAvailabilities::route('/'),
        ];
    }
}
