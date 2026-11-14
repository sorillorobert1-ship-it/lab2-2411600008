<?php

namespace App\Providers;

use App\Models\PurchaseOrder;
use App\Services\ReorderService;
use App\Services\SupplierPerformanceService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public const CACHE_KEYS = ['widgets.pending-orders', 'widgets.recent-orders', 'widgets.reorder-alerts'];

    public function boot(): void
    {
        View::composer('widgets.pending-orders', function ($view) {
            $view->with('pendingOrders', Cache::remember('widgets.pending-orders', now()->addMinutes(5), fn () => [
                'count' => PurchaseOrder::pending()->count(),
                'value' => (float) PurchaseOrder::pending()->sum('total'),
                'drafts' => PurchaseOrder::byStatus('draft')->count(),
            ]));
        });

        View::composer('widgets.recent-orders', function ($view) {
            $view->with('recentOrders', Cache::remember('widgets.recent-orders', now()->addMinutes(5), fn () => PurchaseOrder::with('supplier')->latest('id')->limit(5)->get()));
        });

        View::composer('widgets.supplier-performance', function ($view) {
            $view->with('topSuppliers', app(SupplierPerformanceService::class)->all()->take(5));
        });

        View::composer('widgets.reorder-alerts', function ($view) {
            $view->with('reorderAlerts', Cache::remember('widgets.reorder-alerts', now()->addMinutes(5), function () {
                $service = app(ReorderService::class);

                return $service->getProductsNeedingReorder()->map(fn ($product) => [
                    'product' => $product,
                    'reorder_point' => $service->calculateReorderPoint($product),
                    'suggested_quantity' => $service->calculateOptimalOrderQuantity($product),
                ]);
            }));
        });
    }

    public static function forgetWidgetCache(): void
    {
        foreach (self::CACHE_KEYS as $key) {
            Cache::forget($key);
        }

        SupplierPerformanceService::forget();
    }
}
