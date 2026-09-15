<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Room;
use App\Models\Reservation;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalProducts = Product::count();
        $lowStockItems = Product::whereColumn('quantity', '<=', 'reorder_level')->where('quantity', '>', 0)->count();
        $outOfStockItems = Product::where('quantity', 0)->count();
        $inventoryValue = Product::selectRaw('COALESCE(SUM(quantity * unit_price), 0) as total')
            ->value('total');
        $totalQuantity = Product::sum('quantity');

        $recentProducts = Product::latest()->take(5)->get();

        // Category summary for charts
        $categorySummary = Product::selectRaw('category, SUM(quantity) as total_quantity, SUM(quantity * unit_price) as total_value')
            ->groupBy('category')
            ->get()
            ->keyBy('category');

        // Low stock products for alerts
        $lowStockProducts = Product::whereColumn('quantity', '<=', 'reorder_level')
            ->orderBy('quantity', 'asc')
            ->take(10)
            ->get();

        // Top products by value
        $topProducts = Product::orderByRaw('quantity * unit_price DESC')
            ->take(5)
            ->get();

        // Stock status statistics
        $stockStatistics = [
            'in-stock' => Product::whereColumn('quantity', '>', 'reorder_level')->count(),
            'low-stock' => $lowStockItems,
            'out-of-stock' => $outOfStockItems
        ];

        return view('dashboard', compact(
            'totalProducts',
            'lowStockItems',
            'outOfStockItems',
            'inventoryValue',
            'totalQuantity',
            'recentProducts',
            'categorySummary',
            'lowStockProducts',
            'topProducts',
            'stockStatistics'
        ));
    }

    public function reports(): View
    {
        // Inventory Reports
        $productsByCategory = Product::select('category', \DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get();

        $monthlyValue = Product::select(
                \DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                \DB::raw('SUM(quantity * unit_price) as total_value'),
                \DB::raw('count(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $lowStockByCategory = Product::whereColumn('quantity', '<=', 'reorder_level')
            ->select('category', \DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get();

        $supplierSummary = Product::select('supplier', \DB::raw('count(*) as count'), \DB::raw('SUM(quantity * unit_price) as total_value'))
            ->whereNotNull('supplier')
            ->groupBy('supplier')
            ->orderBy('total_value', 'desc')
            ->get();

        $outOfStockProducts = Product::where('quantity', 0)->get();
        $totalInventoryValue = Product::selectRaw('COALESCE(SUM(quantity * unit_price), 0) as total')->value('total');

        return view('reports', compact(
            'productsByCategory',
            'monthlyValue',
            'lowStockByCategory',
            'supplierSummary',
            'outOfStockProducts',
            'totalInventoryValue'
        ));
    }
}
