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
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Webkul\Contracts\Filament\Resources\HourlyCostCertificationResource\Pages\ManageHourlyCostCertifications;
use Webkul\Contracts\Models\HourlyCostCertification;

class HourlyCostCertificationResource extends Resource
{
    protected static ?string $model = HourlyCostCertification::class;

    protected static ?string $slug = 'contracts/hourly-cost-certifications';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-euro';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'Contracts';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('contract_id')
                    ->relationship('contract', 'reference')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('currency_id')
                    ->relationship('currency', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('certified_hourly_cost')
                    ->numeric()
                    ->required(),
                DatePicker::make('effective_from')->native(false)->required(),
                DatePicker::make('effective_to')->native(false),
                TextInput::make('certificate_reference')->maxLength(255),
                Toggle::make('is_active')->default(true),
                Textarea::make('notes')->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contract.reference')->sortable(),
                TextColumn::make('certified_hourly_cost')->money('EUR')->sortable(),
                TextColumn::make('effective_from')->date()->sortable(),
                TextColumn::make('effective_to')->date()->sortable(),
                IconColumn::make('is_active')->boolean(),
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
            'index' => ManageHourlyCostCertifications::route('/'),
        ];
    }
}
