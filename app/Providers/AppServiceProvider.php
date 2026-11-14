<?php

namespace App\Providers;

use App\Events\StockChanged;
use App\Listeners\NotifyLowStock;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Observers\PurchaseOrderObserver;
use App\Policies\PurchaseOrderPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Policy: model-specific authorization for purchase orders.
        Gate::policy(PurchaseOrder::class, PurchaseOrderPolicy::class);

        // Gates: simple, non-model authorization rules.
        Gate::define('manage-suppliers', fn (User $user) => $user->isManager());
        Gate::define('view-reports', fn (User $user) => $user->isManager());
        Gate::define('run-reorder-check', fn (User $user) => $user->isManager());

        PurchaseOrder::observe(PurchaseOrderObserver::class);

        Event::listen(StockChanged::class, NotifyLowStock::class);
    }
}
