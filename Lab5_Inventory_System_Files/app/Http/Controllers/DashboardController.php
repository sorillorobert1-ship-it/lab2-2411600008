<?php

namespace App\Http\Controllers;

use App\Models\Product;
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
}
