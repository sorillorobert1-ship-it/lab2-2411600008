<div class="card widget-card">
    <div class="card-header d-flex justify-content-between"><span><i class="bi bi-trophy"></i> Top performing suppliers</span>@can('view-reports')<a href="{{ route('reports.supplier-performance') }}" class="text-white small">Full report</a>@endcan</div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Supplier</th><th class="text-end">On-time</th><th class="text-end">Accuracy</th><th class="text-end">Score</th></tr></thead>
            <tbody>
            @forelse ($topSuppliers as $metric)
                <tr><td><a href="{{ route('suppliers.show', $metric['supplier_id']) }}">{{ $metric['supplier'] }}</a></td><td class="text-end">{{ $metric['on_time_rate'] }}%</td><td class="text-end">{{ $metric['accuracy_rate'] }}%</td><td class="text-end fw-semibold">{{ $metric['score'] }}</td></tr>
            @empty
                <tr><td colspan="4" class="text-muted">No delivery data yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
