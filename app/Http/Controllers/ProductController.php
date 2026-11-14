<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Services\ReorderService;
use App\Services\SearchService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request, SearchService $search, ReorderService $reorder)
    {
        $filters = $request->only(['search', 'category', 'supplier_id', 'stock']);

        $products = $search->products($filters)->with('suppliers')->paginate(15)->withQueryString();
        $products->getCollection()->each(fn ($product) => $product->setAttribute('reorder_point', $reorder->calculateReorderPoint($product)));

        return view('products.index', [
            'products' => $products,
            'filters' => $filters,
            'categories' => Product::distinct()->orderBy('category')->pluck('category'),
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
