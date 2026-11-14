@extends('layouts.app')
@section('title', 'Procurement Dashboard')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Procurement Dashboard</h1>
        <p class="text-muted mb-0">Stock risk, approvals and supplier performance at a glance. <span class="small" id="last-refresh"></span></p>
    </div>
    <div class="d-flex gap-2">
        @can('run-reorder-check')
            <form method="POST" action="{{ route('replenishment.check') }}">@csrf<button class="btn btn-outline-primary"><i class="bi bi-arrow-repeat"></i> Run Reorder Check</button></form>
        @endcan
        @can('create', App\Models\PurchaseOrder::class)
            <a class="btn btn-primary" href="{{ route('purchase-orders.create') }}"><i class="bi bi-plus-lg"></i> Create Purchase Order</a>
        @endcan
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="card stat-card p-3"><span class="small text-muted">Active suppliers</span><div class="stat-number">{{ $supplierCount }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="card stat-card accent p-3"><span class="small text-muted">Products tracked</span><div class="stat-number">{{ $productCount }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="card stat-card danger p-3"><span class="small text-muted">Low stock products</span><div class="stat-number">{{ $lowStockCount }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="card stat-card warning p-3"><span class="small text-muted">Open order value</span><div class="stat-number">₱{{ number_format($openOrderValue, 2) }}</div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-4">@include('widgets.pending-orders')</div>
    <div class="col-lg-8">@include('widgets.reorder-alerts')</div>
    <div class="col-lg-7">@include('widgets.recent-orders')</div>
    <div class="col-lg-5">@include('widgets.supplier-performance')</div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const peso = n => '₱' + Number(n).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const badge = (status, label) => `<span class="badge bg-secondary status-${status}">${label}</span>`;

    async function refreshWidgets() {
        try {
            const [pending, recent] = await Promise.all([
                fetch('{{ route('api.dashboard.pending-orders') }}', {headers: {Accept: 'application/json'}}).then(r => r.json()),
                fetch('{{ route('api.dashboard.recent-orders') }}', {headers: {Accept: 'application/json'}}).then(r => r.json()),
            ]);
            document.getElementById('pending-count').textContent = pending.count;
            document.getElementById('pending-value').textContent = peso(pending.value);
            document.getElementById('draft-count').textContent = pending.drafts;
            document.getElementById('recent-orders-body').innerHTML = recent.data.map(o =>
                `<tr><td><a href="${o.url}">${o.reference_number}</a></td><td>${o.supplier ?? ''}</td><td>${badge(o.status, o.status_label)}</td><td class="text-end">${peso(o.total)}</td></tr>`
            ).join('') || '<tr><td colspan="4" class="text-muted">No purchase orders yet.</td></tr>';
            document.getElementById('last-refresh').textContent = 'Auto-refreshed ' + new Date().toLocaleTimeString();
        } catch (e) {
            console.warn('Widget refresh failed', e);
        }
    }

    setInterval(refreshWidgets, 30000);
})();
</script>
@endpush
