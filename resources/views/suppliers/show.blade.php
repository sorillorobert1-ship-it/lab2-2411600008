@extends('layouts.app')
@section('title', $supplier->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="page-title mb-0">{{ $supplier->name }} @unless ($supplier->is_active)<span class="badge bg-secondary fs-6">Inactive</span>@endunless</h1>
        <p class="text-muted mb-0"><code>{{ $supplier->code }}</code> &middot; {{ $supplier->full_contact }}</p>
    </div>
    <div class="d-flex gap-2">
        @can('manage-suppliers')<a class="btn btn-outline-primary" href="{{ route('suppliers.edit', $supplier) }}">Edit</a>@endcan
        <a class="btn btn-outline-secondary" href="{{ route('suppliers.index') }}">Back</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="card widget-card">
            <div class="card-header">Supplier details</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5">Contact person</dt><dd class="col-7">{{ $supplier->contact_person }}</dd>
                    <dt class="col-5">Email</dt><dd class="col-7">{{ $supplier->email }}</dd>
                    <dt class="col-5">Phone</dt><dd class="col-7">{{ $supplier->phone }}</dd>
                    <dt class="col-5">Address</dt><dd class="col-7">{{ $supplier->address ?: '—' }}</dd>
                    <dt class="col-5">Payment terms</dt><dd class="col-7">{{ $supplier->payment_terms }}</dd>
                    <dt class="col-5">Lead time</dt><dd class="col-7">{{ $supplier->lead_time_days }} days</dd>
                    <dt class="col-5">Minimum order</dt><dd class="col-7">₱{{ number_format($supplier->minimum_order_amount, 2) }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card widget-card">
            <div class="card-header">Performance metrics</div>
            <div class="card-body row text-center g-3">
                <div class="col-6 col-md-3"><div class="text-muted small">Total orders</div><div class="stat-number">{{ $performance['total_orders'] }}</div></div>
                <div class="col-6 col-md-3"><div class="text-muted small">On-time delivery</div><div class="stat-number">{{ $performance['on_time_rate'] }}%</div></div>
                <div class="col-6 col-md-3"><div class="text-muted small">Order accuracy</div><div class="stat-number">{{ $performance['accuracy_rate'] }}%</div></div>
                <div class="col-6 col-md-3"><div class="text-muted small">Total value</div><div class="stat-number">₱{{ number_format($performance['total_value'], 0) }}</div></div>
                <div class="col-12 small text-muted">Average delivery: <strong>{{ $performance['average_delivery_days'] ?? '—' }} days</strong> actual vs <strong>{{ $performance['quoted_lead_time'] }} days</strong> quoted &middot; {{ $performance['delivered_orders'] }} delivered order(s)</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <h2 class="h5">Recent purchase orders</h2>
        <table class="table table-sm">
            <thead><tr><th>Reference</th><th>Date</th><th>Status</th><th class="text-end">Total</th></tr></thead>
            <tbody>
            @forelse ($recentOrders as $order)
                <tr><td><a href="{{ route('purchase-orders.show', $order) }}">{{ $order->reference_number }}</a></td><td>{{ $order->order_date->format('M d, Y') }}</td><td><x-status-badge :status="$order->status" /></td><td class="text-end">₱{{ number_format($order->total, 2) }}</td></tr>
            @empty
                <tr><td colspan="4" class="text-muted">No purchase orders yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="col-lg-5">
        <h2 class="h5">Products supplied</h2>
        <table class="table table-sm">
            <thead><tr><th>SKU</th><th>Product</th><th class="text-end">Agreed cost</th><th class="text-end">MOQ</th></tr></thead>
            <tbody>
            @forelse ($supplier->catalogProducts as $product)
                <tr><td>{{ $product->sku }}</td><td>{{ $product->name }} @if ($product->supplier_id === $supplier->id)<span class="badge bg-primary">Primary</span>@endif</td><td class="text-end">₱{{ number_format($product->pivot->unit_cost, 2) }}</td><td class="text-end">{{ $product->pivot->minimum_order_quantity }}</td></tr>
            @empty
                <tr><td colspan="4" class="text-muted">No catalogue products.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
