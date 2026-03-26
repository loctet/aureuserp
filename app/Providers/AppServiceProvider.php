<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Webkul\Security\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Authenticatable::class, User::class);
    }

    public function boot(): void
    {
        if (in_array(config('database.default'), ['mysql', 'mariadb'], true)) {
            Schema::defaultStringLength(191);
        }

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
