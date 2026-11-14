@extends('layouts.app')
@section('title', 'Inventory Tracking')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><h1 class="page-title mb-0">Inventory Tracking</h1><p class="text-muted mb-0">Lab 6 stock movements connected to Lab 7 replenishment.</p></div>
    <x-export-buttons route="exports.transactions" />
</div>

<div class="row g-3">
    @if (auth()->user()->hasRole('manager', 'warehouse'))
    <div class="col-lg-4">
        <form class="card widget-card" method="POST" action="{{ route('inventory.store') }}">
            @csrf
            <div class="card-header">Record stock movement</div>
            <div class="card-body">
                <label class="form-label">Product</label>
                <select name="product_id" class="form-select mb-2" required>
                    @foreach ($products as $product)<option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }} ({{ $product->quantity_on_hand }} on hand)</option>@endforeach
                </select>
                <label class="form-label">Transaction type</label>
                <select name="transaction_type" class="form-select mb-2"><option value="stock_in">Stock in</option><option value="stock_out">Stock out (usage)</option></select>
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" min="1" class="form-control mb-2" required>
                <label class="form-label">Reference document</label>
                <input name="reference_document" class="form-control mb-2" placeholder="e.g. Housekeeping issue slip #123">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
            <div class="card-footer bg-white text-end"><button class="btn btn-primary">Save Movement</button></div>
        </form>
    </div>
    @endif
    <div class="col">
        <x-filter-form :action="route('inventory.index')" :fields="[
            ['name' => 'product_id', 'label' => 'Product', 'type' => 'select', 'options' => $products->pluck('name', 'id')->all()],
            ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => ['in' => 'Stock in', 'out' => 'Stock out', 'adjustment' => 'Adjustment']],
            ['name' => 'user_id', 'label' => 'User', 'type' => 'select', 'options' => $users->pluck('name', 'id')->all()],
            ['name' => 'date_from', 'label' => 'From', 'type' => 'date'],
            ['name' => 'date_to', 'label' => 'To', 'type' => 'date'],
        ]" />
        <div class="table-responsive">
        <table class="table table-sm table-hover align-middle">
            <thead><tr><th>Date</th><th>Product</th><th>Type</th><th class="text-end">Qty</th><th class="text-end">Before</th><th class="text-end">After</th><th>Reference</th><th>User</th></tr></thead>
            <tbody>
            @forelse ($transactions as $tx)
                <tr>
                    <td class="small">{{ $tx->created_at?->format('M d, Y H:i') }}</td>
                    <td>{{ $tx->product?->name }}</td>
                    <td><span class="badge {{ $tx->type === 'in' ? 'bg-success' : ($tx->type === 'out' ? 'bg-danger' : 'bg-secondary') }}">{{ strtoupper($tx->type) }}</span></td>
                    <td class="text-end">{{ $tx->quantity }}</td>
                    <td class="text-end">{{ $tx->quantity_before }}</td>
                    <td class="text-end">{{ $tx->quantity_after }}</td>
                    <td class="small">
                        @if ($tx->reference_type === App\Models\PurchaseOrder::class && $tx->reference_id)
                            <a href="{{ route('purchase-orders.show', $tx->reference_id) }}">{{ $tx->reference_document }}</a>
                        @else
                            {{ $tx->reference_document ?? $tx->notes }}
                        @endif
                    </td>
                    <td class="small">{{ $tx->user?->name ?? 'System' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No transactions found.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
        {{ $transactions->links() }}
    </div>
</div>
@endsection
