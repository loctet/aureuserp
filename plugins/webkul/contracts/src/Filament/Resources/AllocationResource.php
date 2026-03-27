<?php

namespace Webkul\Contracts\Filament\Resources;

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
use Webkul\Contracts\Filament\Resources\AllocationResource\Pages\ManageAllocations;
use Webkul\Contracts\Models\Allocation;

class AllocationResource extends Resource
{
    protected static ?string $model = Allocation::class;

    protected static ?string $slug = 'contracts/allocations';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-plus';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'Contracts';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('project_id')
                    ->relationship('project', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('work_package_id')
                    ->relationship('workPackage', 'name')
                    ->searchable()
                    ->preload(),
                DatePicker::make('month')->native(false)->required(),
                TextInput::make('fte_percent')
                    ->numeric()
                    ->required(),
                TextInput::make('person_months')
                    ->numeric()
                    ->required(),
                Textarea::make('notes')->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')->searchable()->sortable(),
                TextColumn::make('project.name')->searchable()->sortable(),
                TextColumn::make('workPackage.name')->sortable(),
                TextColumn::make('month')->date()->sortable(),
                TextColumn::make('fte_percent')->numeric(2)->suffix('%')->sortable(),
                TextColumn::make('person_months')->numeric(4)->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAllocations::route('/'),
        ];
    }
}
