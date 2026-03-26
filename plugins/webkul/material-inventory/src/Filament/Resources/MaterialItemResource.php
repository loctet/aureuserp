<?php

namespace Webkul\MaterialInventory\Filament\Resources;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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
use Spatie\LaravelSettings\Exceptions\MissingSettings;
use Webkul\MaterialInventory\Enums\MaterialSheetStatus;
use Webkul\MaterialInventory\Filament\Pages\MaterialInventoryDashboard;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource\Pages\CreateMaterialItem;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource\Pages\EditMaterialItem;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource\Pages\ListMaterialItems;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource\Pages\ViewMaterialItem;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource\RelationManagers\TransactionsRelationManager;
use Webkul\MaterialInventory\Models\MaterialItem;
use Webkul\MaterialInventory\Settings\MaterialInventorySettings;
use Webkul\Project\Filament\Resources\ProjectResource;
use Webkul\Security\Filament\Resources\CompanyResource;

class MaterialItemResource extends Resource
{
    protected static ?string $model = MaterialItem::class;

    protected static ?string $slug = 'material-inventory/material-items';

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

    public static function getNavigationUrl(): string
    {
        return MaterialInventoryDashboard::getUrl();
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
            __('material-inventory::filament/resources/material-item.table.columns.sheet_status') => $record->sheet_status?->excelLabel() ?? '—',
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
                            ->options(self::materialCategoryOptions())
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
                            ->options(self::materialStatusOptions())
                            ->default(MaterialSheetStatus::Nuovo->value)
                            ->required(),
                        Toggle::make('is_functional')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.asset.fields.is_functional'))
                            ->dehydrated(fn (): bool => MaterialItem::hasFunctionalColumn())
                            ->visible(fn (): bool => MaterialItem::hasFunctionalColumn())
                            ->default(true),
                        TextInput::make('storage_location')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.asset.fields.storage_location'))
                            ->default(fn () => self::defaultStorageLocation())
                            ->columnSpanFull(),
                        FileUpload::make('images')
                            ->label(__('material-inventory::filament/resources/material-item.form.sections.asset.fields.images'))
                            ->disk('public')
                            ->directory('material-inventory/items')
                            ->multiple()
                            ->image()
                            ->dehydrated(fn (): bool => MaterialItem::hasImagesColumn())
                            ->visible(fn (): bool => MaterialItem::hasImagesColumn())
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
                            ->native(false)
                            ->required(fn () => self::requireExpectedReturnDateOnCheckout()),
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
                            ->formatStateUsing(fn (?MaterialSheetStatus $state) => self::materialStatusOptions()[$state?->value] ?? $state?->excelLabel() ?? ''),
                        TextEntry::make('is_functional')
                            ->label(__('material-inventory::filament/resources/material-item.table.columns.is_functional'))
                            ->formatStateUsing(fn (?bool $state): string => $state ? 'Yes' : 'No'),
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
                        TextEntry::make('is_free')
                            ->formatStateUsing(fn (?bool $state): string => $state ? 'Yes' : 'No'),
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
                    ->formatStateUsing(fn (?MaterialSheetStatus $state) => self::materialStatusOptions()[$state?->value] ?? $state?->excelLabel() ?? ''),
                TextColumn::make('is_functional')
                    ->label(__('material-inventory::filament/resources/material-item.table.columns.is_functional'))
                    ->badge()
                    ->formatStateUsing(fn (?bool $state): string => $state ? 'Yes' : 'No'),
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
                    ->options(self::materialStatusOptions()),
                SelectFilter::make('category')
                    ->options(self::materialCategoryOptions()),
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
                DeleteAction::make(),
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

    public static function materialStatusOptions(): array
    {
        $defaults = collect(MaterialSheetStatus::cases())
            ->mapWithKeys(fn (MaterialSheetStatus $status) => [$status->value => $status->excelLabel()])
            ->all();

        $settings = self::resolveMaterialInventorySettings();

        $configured = collect($settings?->status_list ?? [])
            ->filter(fn (mixed $row): bool => is_array($row) && filled($row['value'] ?? null) && filled($row['label'] ?? null))
            ->mapWithKeys(fn (array $row) => [(string) $row['value'] => (string) $row['label']])
            ->only(array_keys($defaults))
            ->all();

        return $configured !== [] ? $configured : $defaults;
    }

    public static function materialCategoryOptions(): array
    {
        $defaults = [
            'N-Notebook'           => 'N-Notebook',
            'O-Asset Office'       => 'O-Asset Office',
            'L-Licenze SW ufficio' => 'L-Licenze SW ufficio',
            'S-Strumentazione HW'  => 'S-Strumentazione HW',
        ];

        $settings = self::resolveMaterialInventorySettings();

        $configured = collect($settings?->category_list ?? [])
            ->filter(fn (mixed $value): bool => is_string($value) && filled(trim($value)))
            ->mapWithKeys(fn (string $value) => [trim($value) => trim($value)])
            ->all();

        return $configured !== [] ? $configured : $defaults;
    }

    protected static function defaultStorageLocation(): string
    {
        return (string) (self::resolveMaterialInventorySettings()?->default_storage_location ?? '');
    }

    protected static function requireExpectedReturnDateOnCheckout(): bool
    {
        return (bool) (self::resolveMaterialInventorySettings()?->require_expected_return_date_on_checkout ?? false);
    }

    protected static function resolveMaterialInventorySettings(): ?MaterialInventorySettings
    {
        try {
            $settings = app(MaterialInventorySettings::class);

            // Access one property so MissingSettings is raised here (if not migrated yet),
            // allowing the resource to transparently fallback to defaults.
            $settings->category_list;

            return $settings;
        } catch (MissingSettings) {
            return null;
        }
    }
}
