<?php

namespace Webkul\MaterialInventory\Filament\Clusters\Settings\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Webkul\MaterialInventory\Settings\MaterialInventorySettings;
use Webkul\MaterialInventory\Support\MaterialInventoryOptions;
use Webkul\Support\Filament\Clusters\Settings;

class ManageMaterialInventory extends SettingsPage
{
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-vertical';

    protected static ?string $slug = 'material-inventory/manage-settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Material Inventory';

    protected static string $settings = MaterialInventorySettings::class;

    protected static ?string $cluster = Settings::class;

    public function getBreadcrumbs(): array
    {
        return [
            __('material-inventory::filament/clusters/settings/pages/manage-material-inventory.title'),
        ];
    }

    public function getTitle(): string
    {
        return __('material-inventory::filament/clusters/settings/pages/manage-material-inventory.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('material-inventory::filament/clusters/settings/pages/manage-material-inventory.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('categories')
                    ->default(fn () => MaterialInventoryOptions::categories()),

                Textarea::make('categories_text')
                    ->label(__('material-inventory::filament/clusters/settings/pages/manage-material-inventory.form.categories'))
                    ->rows(6)
                    ->dehydrated(false)
                    ->default(fn () => implode(PHP_EOL, MaterialInventoryOptions::categories()))
                    ->afterStateUpdated(function ($state, callable $set): void {
                        $set('categories', self::normalizeLines((string) $state));
                    })
                    ->helperText(__('material-inventory::filament/clusters/settings/pages/manage-material-inventory.form.categories-helper')),

                Hidden::make('statuses')
                    ->default(fn () => MaterialInventoryOptions::statuses()),

                Textarea::make('statuses_text')
                    ->label(__('material-inventory::filament/clusters/settings/pages/manage-material-inventory.form.statuses'))
                    ->rows(6)
                    ->dehydrated(false)
                    ->default(fn () => implode(PHP_EOL, MaterialInventoryOptions::statuses()))
                    ->afterStateUpdated(function ($state, callable $set): void {
                        $set('statuses', self::normalizeLines((string) $state, true));
                    })
                    ->helperText(__('material-inventory::filament/clusters/settings/pages/manage-material-inventory.form.statuses-helper')),

                Select::make('default_status')
                    ->label(__('material-inventory::filament/clusters/settings/pages/manage-material-inventory.form.default-status'))
                    ->options(fn (Get $get) => self::statusOptionsFromState($get('statuses')))
                    ->default(fn () => MaterialInventoryOptions::defaultStatus())
                    ->required(),

                Select::make('status_in_use')
                    ->label(__('material-inventory::filament/clusters/settings/pages/manage-material-inventory.form.status-in-use'))
                    ->options(fn (Get $get) => self::statusOptionsFromState($get('statuses')))
                    ->default(fn () => MaterialInventoryOptions::inUseStatus())
                    ->required(),

                Select::make('status_under_repair')
                    ->label(__('material-inventory::filament/clusters/settings/pages/manage-material-inventory.form.status-under-repair'))
                    ->options(fn (Get $get) => self::statusOptionsFromState($get('statuses')))
                    ->default(fn () => MaterialInventoryOptions::underRepairStatus())
                    ->required(),

                Toggle::make('enforce_project_budget')
                    ->label(__('material-inventory::filament/clusters/settings/pages/manage-material-inventory.form.enforce-project-budget'))
                    ->helperText(__('material-inventory::filament/clusters/settings/pages/manage-material-inventory.form.enforce-project-budget-helper'))
                    ->default(true)
                    ->required(),

                TextInput::make('default_expected_return_days')
                    ->label(__('material-inventory::filament/clusters/settings/pages/manage-material-inventory.form.default-expected-return-days'))
                    ->helperText(__('material-inventory::filament/clusters/settings/pages/manage-material-inventory.form.default-expected-return-days-helper'))
                    ->integer()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
            ]);
    }

    /**
     * @return array<int, string>
     */
    private static function normalizeLines(string $text, bool $snake = false): array
    {
        $lines = preg_split('/\R/u', $text) ?: [];
        $lines = array_values(array_filter(array_map(
            fn (string $value) => trim($snake ? Str::snake($value) : $value),
            $lines
        )));

        return array_values(array_unique($lines));
    }

    /**
     * @param  mixed  $statuses
     * @return array<string, string>
     */
    private static function statusOptionsFromState($statuses): array
    {
        $list = is_array($statuses) ? $statuses : MaterialInventoryOptions::statuses();

        return collect($list)
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->mapWithKeys(fn (string $value) => [$value => MaterialInventoryOptions::humanizeStatus($value)])
            ->all();
    }
}
