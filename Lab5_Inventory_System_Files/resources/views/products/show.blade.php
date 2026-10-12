@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1">{{ $product->name }}</h1>
        <p class="text-muted mb-0">SKU: {{ $product->sku }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">Edit</a>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Product Information</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Category</dt>
                    <dd class="col-sm-8">{{ $product->category }}</dd>

                    <dt class="col-sm-4">Description</dt>
                    <dd class="col-sm-8">{{ $product->description ?: '—' }}</dd>

                    <dt class="col-sm-4">Quantity</dt>
                    <dd class="col-sm-8">
                        @if($product->isOutOfStock())
                            <span class="badge bg-danger">Out of stock</span>
                        @elseif($product->isLowStock())
                            <span class="badge bg-warning text-dark">Low stock: {{ $product->quantity }}</span>
                        @else
                            <span class="badge bg-success">{{ $product->quantity }}</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4">Reorder Level</dt>
                    <dd class="col-sm-8">{{ $product->reorder_level }}</dd>

                    <dt class="col-sm-4">Unit Price</dt>
                    <dd class="col-sm-8">₱{{ number_format((float) $product->unit_price, 2) }}</dd>

                    <dt class="col-sm-4">Supplier</dt>
                    <dd class="col-sm-8">{{ $product->supplier ?: '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Recent Inventory Transactions</h5>
            </div>
            <div class="card-body">
                @forelse($product->inventoryTransactions as $transaction)
                    <div class="border-bottom py-2">
                        <div class="d-flex justify-content-between">
                            <strong>{{ strtoupper(str_replace('_', ' ', $transaction->transaction_type)) }}</strong>
                            <span>{{ $transaction->quantity }}</span>
                        </div>
                        <small class="text-muted">
                            {{ $transaction->created_at?->format('M d, Y h:i A') }}
                            @if($transaction->reference_document)
                                · Ref: {{ $transaction->reference_document }}
                            @endif
                        </small>
                    </div>
                @empty
                    <p class="text-muted mb-0">No inventory transactions recorded yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
