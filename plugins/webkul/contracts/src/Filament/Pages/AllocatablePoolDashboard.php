<?php

namespace Webkul\Contracts\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;
use Webkul\Contracts\Filament\Resources\AllocationResource;
use Webkul\Contracts\Filament\Widgets\AllocatablePoolStatsWidget;
use Webkul\Contracts\Filament\Widgets\ContractExpiryAlertsWidget;

class AllocatablePoolDashboard extends BaseDashboard
{
    protected static string $routePath = 'contracts/allocatable-pool';

    protected static ?int $navigationSort = 0;

    public static function getNavigationGroup(): ?string
    {
        return 'Contracts';
    }

    public static function getNavigationLabel(): string
    {
        return 'Allocatable Pool';
    }

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return 'icon-contracts';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Allocatable Pool';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('manage_allocations')
                ->label('Manage Allocations')
                ->icon('heroicon-o-squares-plus')
                ->url(AllocationResource::getUrl('index')),
        ];
    }

    public function getWidgets(): array
    {
        return [
            AllocatablePoolStatsWidget::class,
            ContractExpiryAlertsWidget::class,
        ];
    }
}
