<div class="card widget-card">
    <div class="card-header"><i class="bi bi-hourglass-split"></i> Pending approvals</div>
    <div class="card-body text-center">
        <div class="display-4 fw-bold text-warning" id="pending-count">{{ $pendingOrders['count'] }}</div>
        <div class="text-muted">orders awaiting approval</div>
        <div class="fs-5 fw-semibold mt-2" id="pending-value">₱{{ number_format($pendingOrders['value'], 2) }}</div>
        <div class="small text-muted mb-3"><span id="draft-count">{{ $pendingOrders['drafts'] }}</span> draft(s) not yet submitted</div>
        <a href="{{ route('purchase-orders.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-primary">Review pending orders</a>
    </div>
</div>
