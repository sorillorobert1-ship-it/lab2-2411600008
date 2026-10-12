@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1">Products</h1>
        <p class="text-muted mb-0">Manage your hardware inventory.</p>
    </div>
    <a href="{{ route('products.create') }}" class="btn btn-primary">+ Add Product</a>
</div>

<!-- FILTERS -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('products.index') }}">
            <div class="row g-2 align-items-end">

                <!-- Category Filter -->
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Stock Status -->
                <div class="col-md-2">
                    <label class="form-label">Stock Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="in-stock" {{ request('status') == 'in-stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low-stock" {{ request('status') == 'low-stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out-of-stock" {{ request('status') == 'out-of-stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>

                <!-- Minimum Price -->
                <div class="col-md-2">
                    <label class="form-label">Min Price</label>
                    <input name="min_price" type="number" min="0" class="form-control" value="{{ request('min_price') }}">
                </div>

                <!-- Maximum Price -->
                <div class="col-md-2">
                    <label class="form-label">Max Price</label>
                    <input name="max_price" type="number" min="0" class="form-control" value="{{ request('max_price') }}">
                </div>

                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label">Search (Name/SKU)</label>
                    <input name="search" class="form-control" placeholder="Search products..." value="{{ request('search') }}">
                </div>

                <!-- BUTTONS -->
                <div class="col-12 d-flex gap-2 flex-wrap mt-2">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Clear Filters</a>
                    <a href="{{ route('products.export.csv') }}" class="btn btn-success">Export CSV</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Qty</th>
                    <th>Reorder</th>
                    <th>Unit Price</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->sku }}</td>
                    <td class="fw-semibold">{{ $product->name }}</td>
                    <td>{{ $product->category }}</td>
                    <td>{{ $product->quantity }}</td>
                    <td>{{ $product->reorder_level }}</td>
                    <td>₱{{ number_format((float) $product->unit_price, 2) }}</td>
                    <td>
                        @if($product->isOutOfStock())
                            <span class="badge bg-danger">Out of Stock</span>
                        @elseif($product->isLowStock())
                            <span class="badge bg-warning text-dark">Low Stock</span>
                        @else
                            <span class="badge bg-success">In Stock</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-secondary">View</a>
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this product?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">No products found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
        <div class="card-footer bg-white">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
