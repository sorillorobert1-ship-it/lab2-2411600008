<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query();

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Stock status filter
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'in-stock':
                    $query->whereColumn('quantity', '>', 'reorder_level');
                    break;
                case 'low-stock':
                    $query->whereColumn('quantity', '<=', 'reorder_level')
                          ->where('quantity', '>', 0);
                    break;
                case 'out-of-stock':
                    $query->where('quantity', 0);
                    break;
            }
        }

        // Price range filter
        if ($request->filled('min_price')) {
            $query->where('unit_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('unit_price', '<=', $request->max_price);
        }

        // Search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('sku', 'like', "%{$searchTerm}%");
            });
        }

        $products = $query->orderBy('name')->paginate(10);
        $categories = Product::select('category')->distinct()->pluck('category')->sort();

        return view('products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            $this->rules(),
            $this->messages()
        );

        Product::create($validated);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product added successfully.');
    }

    public function show(Product $product): View
    {
        $product->load([
            'inventoryTransactions' => fn ($query) => $query
                ->with('user')
                ->latest('created_at')
                ->take(10),
        ]);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate(
            $this->rules($product),
            $this->messages()
        );

        $product->update($validated);

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function exportCsv(): StreamedResponse
    {
        $products = Product::all();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products.csv"',
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, ['ID', 'SKU', 'Name', 'Category', 'Description', 'Quantity', 'Reorder Level', 'Unit Price', 'Supplier', 'Stock Status']);

            // Data rows
            foreach ($products as $product) {
                $status = $product->isOutOfStock() ? 'Out of Stock' : ($product->isLowStock() ? 'Low Stock' : 'In Stock');
                fputcsv($file, [
                    $product->id,
                    $product->sku,
                    $product->name,
                    $product->category,
                    $product->description,
                    $product->quantity,
                    $product->reorder_level,
                    $product->unit_price,
                    $product->supplier,
                    $status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function rules(?Product $product = null): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'sku' => [
                'required',
                'string',
                'max:80',
                Rule::unique('products', 'sku')->ignore($product?->id),
            ],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:150'],
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'sku.required' => 'SKU is required.',
            'sku.unique' => 'This SKU is already being used.',
            'category.required' => 'Please select or enter a category.',
            'quantity.min' => 'Quantity cannot be negative.',
            'reorder_level.min' => 'Reorder level cannot be negative.',
            'unit_price.min' => 'Unit price cannot be negative.',
            'unit_price.numeric' => 'Unit price must be a valid number.',
        ];
    }
}
