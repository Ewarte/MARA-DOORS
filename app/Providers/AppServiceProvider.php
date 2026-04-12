<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();
        Blade::anonymousComponentPath(resource_path('views/admin/components'));
    }
}