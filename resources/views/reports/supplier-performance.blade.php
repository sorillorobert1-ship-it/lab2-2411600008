@extends('layouts.app')
@section('title', 'Supplier Performance Report')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><h1 class="page-title mb-0">Supplier Performance Report</h1><p class="text-muted mb-0">On-time delivery, delivery time vs. quoted lead time, order accuracy and spend (cached 10 minutes).</p></div>
    <x-export-buttons route="exports.supplier-performance" />
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card widget-card"><div class="card-header">On-time delivery &amp; accuracy by supplier</div><div class="card-body"><canvas id="performanceChart" height="220"></canvas></div></div>
    </div>
    <div class="col-lg-6">
        <div class="card widget-card"><div class="card-header">Delivery trend (last 6 months)</div><div class="card-body"><canvas id="trendChart" height="220"></canvas></div></div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card widget-card border-success"><div class="card-header bg-success"><i class="bi bi-arrow-up-circle"></i> Top performing suppliers</div>
            <ul class="list-group list-group-flush">
                @forelse ($topSuppliers as $metric)
                    <li class="list-group-item d-flex justify-content-between"><span>{{ $metric['supplier'] }}</span><strong>{{ $metric['score'] }} pts</strong></li>
                @empty <li class="list-group-item text-muted">No deliveries yet.</li> @endforelse
            </ul>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card widget-card"><div class="card-header bg-danger"><i class="bi bi-arrow-down-circle"></i> Lowest performing suppliers</div>
            <ul class="list-group list-group-flush">
                @forelse ($bottomSuppliers as $metric)
                    <li class="list-group-item d-flex justify-content-between"><span>{{ $metric['supplier'] }}</span><strong>{{ $metric['score'] }} pts</strong></li>
                @empty <li class="list-group-item text-muted">No deliveries yet.</li> @endforelse
            </ul>
        </div>
    </div>
</div>

<h2 class="h5">Performance summary</h2>
<div class="table-responsive">
<table class="table table-hover align-middle">
    <thead><tr><th>Supplier</th><th class="text-end">Total orders</th><th class="text-end">Delivered</th><th class="text-end">On time</th><th class="text-end">On-time %</th><th class="text-end">Quoted lead time</th><th class="text-end">Avg actual delivery</th><th class="text-end">Accuracy %</th><th class="text-end">Total value</th><th class="text-end">Score</th></tr></thead>
    <tbody>
    @foreach ($metrics as $metric)
        <tr>
            <td><a href="{{ route('suppliers.show', $metric['supplier_id']) }}">{{ $metric['supplier'] }}</a></td>
            <td class="text-end">{{ $metric['total_orders'] }}</td>
            <td class="text-end">{{ $metric['delivered_orders'] }}</td>
            <td class="text-end">{{ $metric['on_time_orders'] }}</td>
            <td class="text-end">{{ $metric['on_time_rate'] }}%</td>
            <td class="text-end">{{ $metric['quoted_lead_time'] }} days</td>
            <td class="text-end {{ ($metric['average_delivery_days'] ?? 0) > $metric['quoted_lead_time'] ? 'text-danger' : 'text-success' }}">{{ $metric['average_delivery_days'] !== null ? $metric['average_delivery_days'].' days' : '—' }}</td>
            <td class="text-end">{{ $metric['accuracy_rate'] }}%</td>
            <td class="text-end">₱{{ number_format($metric['total_value'], 2) }}</td>
            <td class="text-end fw-bold">{{ $metric['score'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
<p class="small text-muted">Score = 60% on-time delivery rate + 40% order accuracy rate.</p>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const metrics = @json($metrics);
const trend = @json($trend);
new Chart(document.getElementById('performanceChart'), {
    type: 'bar',
    data: {
        labels: metrics.map(m => m.supplier),
        datasets: [
            {label: 'On-time %', data: metrics.map(m => m.on_time_rate), backgroundColor: '#17365d'},
            {label: 'Accuracy %', data: metrics.map(m => m.accuracy_rate), backgroundColor: '#f4b400'},
        ],
    },
    options: {scales: {y: {beginAtZero: true, max: 100}}},
});
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: trend.map(t => t.month),
        datasets: [
            {label: 'Avg delivery days', data: trend.map(t => t.average_days), borderColor: '#2874a6', backgroundColor: '#2874a633', fill: true, tension: .3},
            {label: 'Deliveries', data: trend.map(t => t.deliveries), borderColor: '#e67e22', tension: .3},
        ],
    },
    options: {scales: {y: {beginAtZero: true}}},
});
</script>
@endpush
