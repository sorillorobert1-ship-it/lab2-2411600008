<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SupplierPerformanceService
{
    public const CACHE_KEY = 'reports.supplier-performance';

    public function calculatePerformance(Supplier $supplier): array
    {
        $orders = $supplier->relationLoaded('purchaseOrders')
            ? $supplier->purchaseOrders
            : $supplier->purchaseOrders()->with('items')->get();

        $placed = $orders->whereNotIn('status', ['draft', 'rejected', 'cancelled']);
        $delivered = $placed->whereIn('status', ['received', 'partially_received'])->filter(fn ($po) => $po->received_at);

        $onTime = $delivered->filter(fn ($po) => $po->expected_delivery_date && $po->received_at->copy()->startOfDay()->lte($po->expected_delivery_date))->count();

        $actualDays = $delivered->map(fn ($po) => $po->order_date->diffInDays($po->received_at->copy()->startOfDay()));

        $accurate = $delivered->filter(fn ($po) => $po->items->every(fn ($item) => $item->received_quantity === $item->quantity))->count();

        $onTimeRate = $delivered->count() ? round($onTime / $delivered->count() * 100, 1) : 0;
        $accuracyRate = $delivered->count() ? round($accurate / $delivered->count() * 100, 1) : 0;

        return [
            'supplier_id' => $supplier->id,
            'supplier' => $supplier->name,
            'total_orders' => $placed->count(),
            'delivered_orders' => $delivered->count(),
            'on_time_orders' => $onTime,
            'on_time_rate' => $onTimeRate,
            'quoted_lead_time' => $supplier->lead_time_days,
            'average_delivery_days' => $actualDays->isEmpty() ? null : round($actualDays->avg(), 1),
            'accuracy_rate' => $accuracyRate,
            'total_value' => (float) $placed->sum('total'),
            'score' => round(($onTimeRate * 0.6) + ($accuracyRate * 0.4), 1),
        ];
    }

    /** Cached performance metrics for every active supplier, best score first. */
    public function all(): Collection
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(10), function () {
            return Supplier::active()
                ->with('purchaseOrders.items')
                ->get()
                ->map(fn (Supplier $supplier) => $this->calculatePerformance($supplier))
                ->sortByDesc('score')
                ->values();
        });
    }

    /** Average delivery days per month for the delivery trend chart. */
    public function monthlyDeliveryTrend(int $months = 6): Collection
    {
        return Cache::remember(self::CACHE_KEY.'.trend', now()->addMinutes(10), function () use ($months) {
            $orders = PurchaseOrder::whereIn('status', ['received', 'partially_received'])
                ->whereNotNull('received_at')
                ->where('received_at', '>=', now()->subMonths($months)->startOfMonth())
                ->get();

            return collect(range($months - 1, 0))->map(function ($ago) use ($orders) {
                $month = now()->subMonths($ago);
                $inMonth = $orders->filter(fn ($po) => $po->received_at->isSameMonth($month));

                return [
                    'month' => $month->format('M Y'),
                    'deliveries' => $inMonth->count(),
                    'average_days' => $inMonth->isEmpty() ? 0 : round($inMonth->avg(fn ($po) => $po->order_date->diffInDays($po->received_at->copy()->startOfDay())), 1),
                ];
            });
        });
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::CACHE_KEY.'.trend');
    }
}
