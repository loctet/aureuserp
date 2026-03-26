<?php

namespace Webkul\BulkImport;

use Filament\Contracts\Plugin;
use Filament\Panel;

class BulkImportPlugin implements Plugin
{
    public function getId(): string
    {
        return 'bulk-import';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
