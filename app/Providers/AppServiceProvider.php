<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\Supplier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Gate::define('approve-purchase-order', fn ($user, PurchaseOrder $order) => $user->isManager() && in_array($order->status, ['pending_approval','draft'], true));
        Gate::define('manage-suppliers', fn ($user) => $user->isManager());
        Gate::policy(PurchaseOrder::class, \App\Policies\PurchaseOrderPolicy::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('dashboard', function ($view) { $view->with('topSuppliers', Cache::remember('dashboard.top-suppliers', now()->addMinutes(10), fn () => Supplier::where('is_active', true)->orderByDesc('reliability_score')->limit(3)->get())); });
    }
}
