<div class="card widget-card">
    <div class="card-header d-flex justify-content-between"><span><i class="bi bi-receipt"></i> Recent purchase orders</span><a href="{{ route('purchase-orders.index') }}" class="text-white small">View all</a></div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Reference</th><th>Supplier</th><th>Status</th><th class="text-end">Total</th></tr></thead>
            <tbody id="recent-orders-body">
            @forelse ($recentOrders as $order)
                <tr><td><a href="{{ route('purchase-orders.show', $order) }}">{{ $order->reference_number }}</a></td><td>{{ $order->supplier->name }}</td><td><x-status-badge :status="$order->status" /></td><td class="text-end">₱{{ number_format($order->total, 2) }}</td></tr>
            @empty
                <tr><td colspan="4" class="text-muted">No purchase orders yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
