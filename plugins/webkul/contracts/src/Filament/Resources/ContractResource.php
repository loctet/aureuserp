<?php

namespace Webkul\Contracts\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Webkul\Contracts\Filament\Resources\ContractResource\Pages\ManageContracts;
use Webkul\Contracts\Filament\Resources\ContractResource\RelationManagers\HourlyCostCertificationsRelationManager;
use Webkul\Contracts\Models\Contract;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static ?string $slug = 'contracts/contracts';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 2;

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
                Select::make('contract_type_id')
                    ->relationship('contractType', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('reference')->maxLength(255),
                Select::make('status')
                    ->options([
                        'active'     => 'Active',
                        'inactive'   => 'Inactive',
                        'terminated' => 'Terminated',
                    ])
                    ->default('active')
                    ->required(),
                DatePicker::make('start_date')->native(false)->required(),
                DatePicker::make('end_date')->native(false),
                DatePicker::make('renewal_deadline')->native(false),
                Textarea::make('notes')->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->searchable()->sortable(),
                TextColumn::make('employee.name')->searchable()->sortable(),
                TextColumn::make('contractType.name')->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('start_date')->date()->sortable(),
                TextColumn::make('end_date')->date()->sortable(),
                TextColumn::make('renewal_deadline')->date()->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            HourlyCostCertificationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageContracts::route('/'),
        ];
    }
}
