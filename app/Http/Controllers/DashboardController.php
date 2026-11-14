<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\ReorderService;
use App\Services\SupplierPerformanceService;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'supplierCount' => Supplier::active()->count(),
            'productCount' => Product::active()->count(),
            'lowStockCount' => Product::active()->lowStock()->count(),
            'openOrderValue' => (float) PurchaseOrder::whereIn('status', ['approved', 'sent', 'partially_received'])->sum('total'),
        ]);
    }

    public function runReorderCheck(ReorderService $reorderService)
    {
        $this->authorize('run-reorder-check');

        $orders = $reorderService->generatePurchaseOrders(auth()->user());

        return redirect()->route('dashboard')->with('success', "Reorder check complete: {$orders->count()} draft purchase order(s) generated.");
    }

    public function pendingOrders()
    {
        return response()->json([
            'count' => PurchaseOrder::pending()->count(),
            'value' => (float) PurchaseOrder::pending()->sum('total'),
            'drafts' => PurchaseOrder::byStatus('draft')->count(),
            'orders' => PurchaseOrderResource::collection(PurchaseOrder::pending()->with('supplier')->latest('id')->limit(5)->get()),
        ]);
    }

    public function recentOrders()
    {
        return PurchaseOrderResource::collection(PurchaseOrder::with('supplier')->latest('id')->limit(5)->get());
    }

    public function supplierPerformance(SupplierPerformanceService $performance)
    {
        return response()->json(['data' => $performance->all()->take(5)->values()]);
    }

    public function reorderAlerts(ReorderService $reorderService)
    {
        return response()->json(['data' => $reorderService->getProductsNeedingReorder()->map(fn ($product) => [
            'product' => new ProductResource($product),
            'reorder_point' => $reorderService->calculateReorderPoint($product),
            'suggested_quantity' => $reorderService->calculateOptimalOrderQuantity($product),
        ])]);
    }
}
