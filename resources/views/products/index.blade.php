@extends('layouts.app')
@section('title', 'Products')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><h1 class="page-title mb-0">Products</h1><p class="text-muted mb-0">Hotel room and guest supplies with calculated reorder points.</p></div>
    <x-export-buttons route="exports.products" />
</div>

<x-filter-form :action="route('products.index')" :fields="[
    ['name' => 'search', 'label' => 'Name or SKU'],
    ['name' => 'category', 'label' => 'Category', 'type' => 'select', 'options' => $categories->mapWithKeys(fn ($c) => [$c => $c])->all()],
    ['name' => 'supplier_id', 'label' => 'Supplier', 'type' => 'select', 'options' => $suppliers->pluck('name', 'id')->all()],
    ['name' => 'stock', 'label' => 'Stock', 'type' => 'select', 'options' => ['low' => 'Low stock only']],
]" />

<div class="table-responsive">
<table class="table table-hover align-middle">
    <thead><tr><th>SKU</th><th>Product</th><th>Category</th><th>Primary supplier</th><th class="text-end">On hand</th><th class="text-end">Reorder level</th><th class="text-end">Reorder point</th><th class="text-end">Unit cost</th><th>Status</th></tr></thead>
    <tbody>
    @forelse ($products as $product)
        <tr>
            <td><code>{{ $product->sku }}</code></td>
            <td>{{ $product->name }}</td>
            <td>{{ $product->category }}</td>
            <td>{{ $product->supplier?->name ?? '—' }} @if ($product->suppliers->count() > 1)<span class="badge bg-light text-dark border">+{{ $product->suppliers->count() - 1 }} alt</span>@endif</td>
            <td class="text-end">{{ $product->quantity_on_hand }} {{ $product->unit }}</td>
            <td class="text-end">{{ $product->reorder_level }}</td>
            <td class="text-end">{{ $product->reorder_point }}</td>
            <td class="text-end">₱{{ number_format($product->unit_cost, 2) }}</td>
            <td><span class="badge {{ $product->isOutOfStock() ? 'bg-danger' : ($product->isLowStock() ? 'bg-warning text-dark' : 'bg-success') }}">{{ $product->stock_status }}</span></td>
        </tr>
    @empty
        <tr><td colspan="9" class="text-center text-muted py-4">No products found.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $products->links() }}
@endsection
