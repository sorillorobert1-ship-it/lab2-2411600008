<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\SearchService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request, SearchService $search)
    {
        $filters = $request->only(['product_id', 'type', 'user_id', 'date_from', 'date_to']);

        return view('inventory.index', [
            'products' => Product::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'transactions' => $search->transactions($filters)->paginate(15)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function store(Request $request, InventoryService $service)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'transaction_type' => 'required|in:stock_in,stock_out',
            'quantity' => 'required|integer|min:1',
            'reference_document' => 'nullable|max:150',
            'notes' => 'nullable|max:1000',
        ]);

        $service->move((int) $data['product_id'], $request->user()->id, $data['transaction_type'], (int) $data['quantity'], $data['reference_document'] ?? null, $data['notes'] ?? null);

        return back()->with('success', 'Stock movement saved and low-stock checks dispatched.');
    }

    public function api()
    {
        return response()->json([
            'updated_at' => now()->toIso8601String(),
            'total_products' => Product::count(),
            'total_quantity' => (int) Product::sum('quantity_on_hand'),
            'low_stock' => Product::lowStock()->count(),
            'inventory_value' => (float) Product::selectRaw('COALESCE(SUM(quantity_on_hand * unit_cost),0) as total')->value('total'),
            'recent_transactions' => InventoryTransaction::with('product')->latest('created_at')->limit(5)->get(),
        ]);
    }
}
