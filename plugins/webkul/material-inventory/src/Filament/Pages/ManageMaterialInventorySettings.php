<?php

namespace Webkul\MaterialInventory\Filament\Pages;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Webkul\MaterialInventory\Enums\MaterialSheetStatus;
use Webkul\MaterialInventory\Settings\MaterialInventorySettings;

class ManageMaterialInventorySettings extends SettingsPage
{
    protected static ?string $slug = 'material-inventory/manage-material-inventory-settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 10;

    protected static string $settings = MaterialInventorySettings::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.material-inventory');
    }

    public static function getNavigationLabel(): string
    {
        return __('material-inventory::filament/pages/manage-material-inventory-settings.navigation.label');
    }

    public function getTitle(): string
    {
        return __('material-inventory::filament/pages/manage-material-inventory-settings.title');
    }

    public function getBreadcrumbs(): array
    {
        return [
            __('material-inventory::filament/pages/manage-material-inventory-settings.title'),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('material-inventory::filament/pages/manage-material-inventory-settings.sections.statuses.title'))
                    ->description(__('material-inventory::filament/pages/manage-material-inventory-settings.sections.statuses.description'))
                    ->schema([
                        Repeater::make('status_list')
                            ->label(__('material-inventory::filament/pages/manage-material-inventory-settings.sections.statuses.fields.status_list'))
                            ->minItems(1)
                            ->reorderable(false)
                            ->schema([
                                Select::make('value')
                                    ->label(__('material-inventory::filament/pages/manage-material-inventory-settings.sections.statuses.fields.value'))
                                    ->options(collect(MaterialSheetStatus::cases())->mapWithKeys(
                                        fn (MaterialSheetStatus $status) => [$status->value => $status->excelLabel()]
                                    ))
                                    ->required(),
                                TextInput::make('label')
                                    ->label(__('material-inventory::filament/pages/manage-material-inventory-settings.sections.statuses.fields.label'))
                                    ->required(),
                            ])
                            ->columns(2),
                    ]),
                Section::make(__('material-inventory::filament/pages/manage-material-inventory-settings.sections.categories.title'))
                    ->schema([
                        Repeater::make('category_list')
                            ->label(__('material-inventory::filament/pages/manage-material-inventory-settings.sections.categories.fields.category_list'))
                            ->minItems(1)
                            ->reorderable(false)
                            ->schema([
                                TextInput::make('value')
                                    ->label(__('material-inventory::filament/pages/manage-material-inventory-settings.sections.categories.fields.value'))
                                    ->required(),
                            ]),
                    ]),
                Section::make(__('material-inventory::filament/pages/manage-material-inventory-settings.sections.other.title'))
                    ->schema([
                        TextInput::make('default_storage_location')
                            ->label(__('material-inventory::filament/pages/manage-material-inventory-settings.sections.other.fields.default_storage_location')),
                        Toggle::make('require_expected_return_date_on_checkout')
                            ->label(__('material-inventory::filament/pages/manage-material-inventory-settings.sections.other.fields.require_expected_return_date_on_checkout')),
                    ]),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['category_list'] = collect($data['category_list'] ?? [])
            ->map(fn (string $value) => ['value' => $value])
            ->values()
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['status_list'] = collect($data['status_list'] ?? [])
            ->filter(fn (array $row): bool => filled($row['value'] ?? null) && filled($row['label'] ?? null))
            ->values()
            ->all();

        $data['category_list'] = collect($data['category_list'] ?? [])
            ->map(fn (array $row): ?string => filled($row['value'] ?? null) ? trim((string) $row['value']) : null)
            ->filter()
            ->values()
            ->all();

        return $data;
    }
}
