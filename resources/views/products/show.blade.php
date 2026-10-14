@extends('layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1">{{ $product->name }}</h1>
        <p class="text-muted mb-0">SKU: {{ $product->sku }}</p>
    </div>
    <div>
        <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">Edit</a>
        <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Delete this product?');">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">Delete</button>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Product Information</h5>
                <table class="table">
                    <tr>
                        <th width="30%">SKU</th>
                        <td>{{ $product->sku }}</td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td>{{ $product->category }}</td>
                    </tr>
                    <tr>
                        <th>Supplier</th>
                        <td>{{ $product->supplier ?: '—' }}</td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>{{ $product->description ?: '—' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Stock Information</h5>
                <div class="mb-3">
                    <div class="text-muted small">Current Quantity</div>
                    <div class="h3 mb-0">{{ $product->quantity }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Reorder Level</div>
                    <div class="h4 mb-0">{{ $product->reorder_level }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Unit Price</div>
                    <div class="h4 mb-0">₱{{ number_format((float) $product->unit_price, 2) }}</div>
                </div>
                <div>
                    <div class="text-muted small mb-1">Stock Status</div>
                    @if($product->isOutOfStock())
                        <span class="badge bg-danger fs-6">Out of Stock</span>
                    @elseif($product->isLowStock())
                        <span class="badge bg-warning text-dark fs-6">Low Stock</span>
                    @else
                        <span class="badge bg-success fs-6">In Stock</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Transaction History</h5>
            <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-primary">Record Movement</a>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead><tr><th>Date</th><th>Type</th><th>Quantity</th><th>Running Balance</th><th>User</th><th>Reference</th></tr></thead>
                <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->created_at?->format('Y-m-d H:i') }}</td>
                        <td><span class="badge {{ $transaction->transaction_type === 'stock_in' ? 'bg-success' : 'bg-danger' }}">{{ str_replace('_', ' ', $transaction->transaction_type) }}</span></td>
                        <td>{{ $transaction->transaction_type === 'stock_in' ? '+' : '-' }}{{ $transaction->quantity }}</td>
                        <td>{{ $transaction->quantity_after }}</td>
                        <td>{{ $transaction->user?->name ?? 'Unknown' }}</td>
                        <td>{{ $transaction->reference_document ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">No inventory transactions yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $transactions->links() }}
    </div>
</div>

<div class="mt-4">
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Back to Products</a>
</div>
@endsection
