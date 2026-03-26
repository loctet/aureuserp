<?php

namespace Webkul\MaterialInventory\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;
use Webkul\MaterialInventory\Filament\Resources\MaterialItemResource;
use Webkul\MaterialInventory\Filament\Widgets\InventoryLifecycleChartWidget;
use Webkul\MaterialInventory\Filament\Widgets\MaterialsByCategoryChartWidget;
use Webkul\MaterialInventory\Filament\Widgets\MaterialsPerProjectReportWidget;
use Webkul\MaterialInventory\Filament\Widgets\MaterialsByStatusChartWidget;
use Webkul\MaterialInventory\Filament\Widgets\MaterialsOverviewStatsWidget;

class MaterialInventoryDashboard extends BaseDashboard
{
    protected static string $routePath = 'material-inventory';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getNavigationLabel(): string
    {
        return __('material-inventory::filament/pages/dashboard.navigation.label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.material-inventory');
    }

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return 'icon-material-inventory';
    }

    public function getTitle(): string|Htmlable
    {
        return __('material-inventory::filament/pages/dashboard.title');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_materials')
                ->label(__('material-inventory::filament/pages/dashboard.actions.view_materials'))
                ->icon('heroicon-o-clipboard-document-list')
                ->url(MaterialItemResource::getUrl('index')),
            Action::make('new_material')
                ->label(__('material-inventory::filament/pages/dashboard.actions.new_material'))
                ->icon('heroicon-o-plus')
                ->url(MaterialItemResource::getUrl('create')),
        ];
    }

    public function getWidgets(): array
    {
        return [
            MaterialsOverviewStatsWidget::class,
            MaterialsByStatusChartWidget::class,
            MaterialsByCategoryChartWidget::class,
            InventoryLifecycleChartWidget::class,
            MaterialsPerProjectReportWidget::class,
        ];
    }
}

