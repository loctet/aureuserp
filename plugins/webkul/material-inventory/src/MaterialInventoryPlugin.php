<?php

namespace Webkul\MaterialInventory;

use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Webkul\MaterialInventory\Filament\Clusters\Settings\Pages\ManageMaterialInventory;
use Webkul\PluginManager\Package;

class MaterialInventoryPlugin implements Plugin
{
    public function getId(): string
    {
        return 'material-inventory';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        if (! Package::isPluginInstalled($this->getId())) {
            return;
        }

        $panel
            ->when($panel->getId() == 'admin', function (Panel $panel) {
                $panel
                    ->discoverResources(
                        in: __DIR__.'/Filament/Resources',
                        for: 'Webkul\\MaterialInventory\\Filament\\Resources'
                    )
                    ->discoverPages(
                        in: __DIR__.'/Filament/Pages',
                        for: 'Webkul\\MaterialInventory\\Filament\\Pages'
                    )
                    ->discoverClusters(
                        in: __DIR__.'/Filament/Clusters',
                        for: 'Webkul\\MaterialInventory\\Filament\\Clusters'
                    )
                    ->discoverWidgets(
                        in: __DIR__.'/Filament/Widgets',
                        for: 'Webkul\\MaterialInventory\\Filament\\Widgets'
                    )
                    ->navigationItems([
                        NavigationItem::make('settings')
                            ->label(fn () => __('material-inventory::app.navigation.settings.label'))
                            ->url(fn () => ManageMaterialInventory::getUrl())
                            ->group(__('material-inventory::app.navigation.settings.group'))
                            ->sort(4)
                            ->visible(fn () => ManageMaterialInventory::canAccess()),
                    ]);
            });
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
