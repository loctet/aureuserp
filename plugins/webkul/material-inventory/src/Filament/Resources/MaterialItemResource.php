<?php

namespace Webkul\MaterialInventory\Filament\Resources;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource\Pages\CreateMaterialItem;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource\Pages\EditMaterialItem;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource\Pages\ListMaterialItems;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource\Pages\ViewMaterialItem;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource\RelationManagers\TransactionsRelationManager;
use Webkul\MaterialInventory\Models\MaterialItem;
use Webkul\MaterialInventory\Support\MaterialInventoryOptions;
use Webkul\Project\Filament\Resources\ProjectResource;
use Webkul\Security\Filament\Resources\CompanyResource;

class MaterialItemResource extends Resource
{
    protected static ?string $model = MaterialItem::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return __('material-inventory::filament/resources/material-item.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('material-inventory::filament/resources/material-item.navigation.label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.material-inventory');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'inventory_number',
            'name',
            'serial_number',
            'category',
            'manufacturer',
            'model',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var MaterialItem $record */
        return [
            __('material-inventory::filament/resources/material-item.table.columns.sheet_status') => MaterialInventoryOptions::humanizeStatus((string) $record->sheet_status),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('material-inventory::filament/resources/material-item.form.sections.identity.title'))
                    ->schema([
                        Select::make('company_id')
                            ->relationship('company', 'name')
                            ->default(fn () => Auth::user()?->default_company_id)
                            ->required()
                            ->preload()
                            ->searchable()
                            ->createOptionForm(fn (Schema $schema) => CompanyResource::form($schema)),
                        TextInput::make('inventory_number')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.identity.fields.inventory_number'))
                            ->helperText('Automatically generated and unique.')
                            ->disabled()
                            ->maxLength(64),
                        TextInput::make('progressive_asset_number')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.identity.fields.progressive_asset_number'))
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (?MaterialItem $record) => $record !== null),
                        Toggle::make('inventory_number_locked')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.identity.fields.inventory_number_locked'))
                            ->disabled()
                            ->visible(fn (?MaterialItem $record) => $record !== null && $record->inventory_number_locked),
                    ])
                    ->columns(2),
                Section::make(__('material-inventory::filament/resources/material-item.form.sections.asset.title'))
                    ->schema([
                        Select::make('category')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.asset.fields.category'))
                            ->options(fn () => MaterialInventoryOptions::categoryOptions())
                            ->searchable()
                            ->required(),
                        DatePicker::make('acquisition_date')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.asset.fields.acquisition_date'))
                            ->native(false),
                        TextInput::make('name')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.asset.fields.name'))
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),
                        TextInput::make('manufacturer')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.asset.fields.manufacturer')),
                        TextInput::make('model')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.asset.fields.model')),
                        TextInput::make('serial_number')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.asset.fields.serial_number')),
                        TextInput::make('supplier')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.asset.fields.supplier')),
                        Select::make('sheet_status')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.asset.fields.sheet_status'))
                            ->options(fn () => MaterialInventoryOptions::statusOptions())
                            ->default(fn () => MaterialInventoryOptions::defaultStatus())
                            ->required(),
                        TextInput::make('storage_location')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.asset.fields.storage_location'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make(__('material-inventory::filament/resources/material-item.form.sections.custody.title'))
                    ->schema([
                        Placeholder::make('_custody_helper')
                            ->label('')
                            ->content(__('material-inventory::filament/resources/material-item.form.sections.custody.helper'))
                            ->columnSpanFull()
                            ->visibleOn('edit'),
                        Select::make('current_custodian_employee_id')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.custody.fields.current_custodian_employee_id'))
                            ->relationship('currentCustodian', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled(),
                        DatePicker::make('assignment_date')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.custody.fields.assignment_date'))
                            ->native(false)
                            ->disabled(),
                        DatePicker::make('expected_return_date')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.custody.fields.expected_return_date'))
                            ->native(false),
                        Select::make('project_id')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.custody.fields.project_id'))
                            ->relationship('project', 'name', fn (Builder $query) => $query->orderBy('name'))
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => ProjectResource::form($schema)),
                        TextInput::make('acquisition_cost')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.custody.fields.acquisition_cost'))
                            ->numeric(),
                        Toggle::make('is_free')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.custody.fields.is_free'))
                            ->default(false),
                        Textarea::make('notes')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.custody.fields.notes'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('material-inventory::filament/resources/material-item.form.sections.identity.title'))
                    ->schema([
                        TextEntry::make('inventory_number')->label(__('material-inventory::filament/resources/material-item.table.columns.inventory_number')),
                        TextEntry::make('progressive_asset_number')->label(__('material-inventory::filament/resources/material-item.table.columns.progressive_asset_number')),
                        TextEntry::make('company.name')->label('Company'),
                    ])
                    ->columns(3),
                Section::make(__('material-inventory::filament/resources/material-item.form.sections.asset.title'))
                    ->schema([
                        TextEntry::make('category'),
                        TextEntry::make('acquisition_date')->date('d/m/Y'),
                        TextEntry::make('name')->columnSpanFull(),
                        TextEntry::make('manufacturer'),
                        TextEntry::make('model'),
                        TextEntry::make('serial_number'),
                        TextEntry::make('supplier'),
                        TextEntry::make('sheet_status')
                            ->formatStateUsing(fn (?string $state) => MaterialInventoryOptions::humanizeStatus((string) $state)),
                        TextEntry::make('storage_location')->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make(__('material-inventory::filament/resources/material-item.form.sections.custody.title'))
                    ->schema([
                        TextEntry::make('currentCustodian.name')->label(__('material-inventory::filament/resources/material-item.table.columns.custodian')),
                        TextEntry::make('assignment_date')->date('d/m/Y'),
                        TextEntry::make('expected_return_date')->date('d/m/Y'),
                        TextEntry::make('project.name')->label(__('material-inventory::filament/resources/material-item.table.columns.project')),
                        TextEntry::make('acquisition_cost')->money('EUR'),
                        TextEntry::make('is_free')->boolean(),
                        TextEntry::make('notes')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('inventory_number')
                    ->label(__('material-inventory::filament/resources/material-item.table.columns.inventory_number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('progressive_asset_number')
                    ->label(__('material-inventory::filament/resources/material-item.table.columns.progressive_asset_number'))
                    ->sortable(),
                TextColumn::make('category')
                    ->label(__('material-inventory::filament/resources/material-item.table.columns.category'))
                    ->toggleable(),
                TextColumn::make('acquisition_date')
                    ->label(__('material-inventory::filament/resources/material-item.table.columns.acquisition_date'))
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('material-inventory::filament/resources/material-item.table.columns.name'))
                    ->searchable()
                    ->limit(40),
                TextColumn::make('manufacturer')
                    ->label(__('material-inventory::filament/resources/material-item.table.columns.manufacturer')),
                TextColumn::make('sheet_status')
                    ->label(__('material-inventory::filament/resources/material-item.table.columns.sheet_status'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => MaterialInventoryOptions::humanizeStatus((string) $state)),
                TextColumn::make('storage_location')
                    ->label(__('material-inventory::filament/resources/material-item.table.columns.storage_location'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('currentCustodian.name')
                    ->label(__('material-inventory::filament/resources/material-item.table.columns.custodian')),
                TextColumn::make('assignment_date')
                    ->label(__('material-inventory::filament/resources/material-item.table.columns.assignment_date'))
                    ->date('d/m/Y'),
                TextColumn::make('project.name')
                    ->label(__('material-inventory::filament/resources/material-item.table.columns.project')),
            ])
            ->filters([
                SelectFilter::make('sheet_status')
                    ->options(fn () => MaterialInventoryOptions::statusOptions()),
                SelectFilter::make('category')
                    ->options(fn () => MaterialInventoryOptions::categoryOptions()),
                SelectFilter::make('project_id')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('current_custodian_employee_id')
                    ->relationship('currentCustodian', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('inventory_number')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListMaterialItems::route('/'),
            'create' => CreateMaterialItem::route('/create'),
            'view'   => ViewMaterialItem::route('/{record}'),
            'edit'   => EditMaterialItem::route('/{record}/edit'),
        ];
    }
}
