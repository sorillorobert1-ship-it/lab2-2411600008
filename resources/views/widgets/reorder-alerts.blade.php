<div class="card widget-card">
    <div class="card-header"><i class="bi bi-exclamation-triangle"></i> Reorder alerts <span class="badge bg-danger">{{ $reorderAlerts->count() }}</span></div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Product</th><th>Supplier</th><th class="text-end">On hand</th><th class="text-end">Reorder point</th><th class="text-end">Suggested qty</th></tr></thead>
            <tbody>
            @forelse ($reorderAlerts as $alert)
                <tr class="{{ $alert['product']->quantity_on_hand <= 0 ? 'table-danger' : '' }}">
                    <td>{{ $alert['product']->name }} <small class="text-muted">{{ $alert['product']->sku }}</small></td>
                    <td>{{ $alert['product']->supplier->name }}</td>
                    <td class="text-end text-danger fw-semibold">{{ $alert['product']->quantity_on_hand }} {{ $alert['product']->unit }}</td>
                    <td class="text-end">{{ $alert['reorder_point'] }}</td>
                    <td class="text-end">{{ $alert['suggested_quantity'] }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted p-3">All products are above their reorder points (or already on an open purchase order).</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
