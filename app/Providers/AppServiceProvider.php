<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::listen(\App\Events\StockChanged::class, \App\Listeners\NotifyLowStock::class);
        Paginator::useBootstrapFive();
    }
}
