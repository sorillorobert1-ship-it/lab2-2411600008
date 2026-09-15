@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1">Inventory Dashboard</h1>
        <p class="text-muted mb-0">Real-time inventory statistics.</p>
    </div>
    <a href="{{ route('products.create') }}" class="btn btn-primary">+ Add Product</a>
</div>

<!-- LOW STOCK BANNER -->
@if($lowStockItems > 0)
<div class="alert alert-danger">
    <strong>⚠ Low Stock Alert:</strong>
    {{ $lowStockItems }} item(s) need attention.
</div>
@endif

<!-- STATISTICS CARDS -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="small">Products</div>
            <div class="stat-number">{{ $totalProducts }}</div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="small">Total Quantity</div>
            <div class="stat-number">{{ $totalQuantity }}</div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="small">Inventory Value</div>
            <div class="stat-number">₱{{ number_format((float) $inventoryValue, 2) }}</div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="small">Low / Out Stock</div>
            <div class="stat-number">{{ $lowStockItems + $outOfStockItems }}</div>
        </div>
    </div>
</div>

<!-- CHARTS -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="chart-card">
            <h5>Inventory Value by Category</h5>
            <div class="chart-wrap">
                <canvas id="categoryValueChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="chart-card">
            <h5>Stock Status Distribution</h5>
            <div class="chart-wrap">
                <canvas id="stockStatusChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="chart-card">
            <h5>Top Products by Value</h5>
            <div class="chart-wrap">
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="chart-card">
            <h5>Category Quantity Distribution</h5>
            <div class="chart-wrap">
                <canvas id="categoryQuantityChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- RECENT PRODUCTS TABLE -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h5>Recently Added Products</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Reorder</th>
                        <th>Unit Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($recentProducts as $product)
                    <tr>
                        <td>{{ $product->sku }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category }}</td>
                        <td>{{ $product->quantity }}</td>
                        <td>{{ $product->reorder_level }}</td>
                        <td>₱{{ number_format((float) $product->unit_price, 2) }}</td>
                        <td>
                            @if($product->isOutOfStock())
                                <span class="badge bg-danger">Out of Stock</span>
                            @elseif($product->isLowStock())
                                <span class="badge bg-warning text-dark">Low Stock</span>
                            @else
                                <span class="badge bg-success">In Stock</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No products yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ALERTS AND CHANGES -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Low Stock Alerts</h5>
                <div id="alertsList">
                    @forelse($lowStockProducts as $product)
                        <div class="change-item">
                            <strong>{{ $product->name }}</strong> ({{ $product->sku }})
                            <br>
                            <small class="text-muted">
                                Current: {{ $product->quantity }} | Reorder Level: {{ $product->reorder_level }}
                            </small>
                        </div>
                    @empty
                        <p class="text-muted">No low stock alerts.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Recent Inventory Changes</h5>
                <div id="changesList">
                    <p class="text-muted">Track inventory movements here.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    // Category Summary Data
    const categorySummary = @json($categorySummary);
    const stockStatistics = @json($stockStatistics);
    const topProducts = @json($topProducts);

    // Category Value Chart
    new Chart(document.getElementById('categoryValueChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(categorySummary),
            datasets: [{
                label: 'Inventory Value (₱)',
                data: Object.values(categorySummary).map(item => item.total_value),
                backgroundColor: ['#17365d', '#2874a6', '#5dade2', '#85c1e9', '#aed6f1'],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });

    // Stock Status Chart
    new Chart(document.getElementById('stockStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['In Stock', 'Low Stock', 'Out of Stock'],
            datasets: [{
                data: [stockStatistics['in-stock'], stockStatistics['low-stock'], stockStatistics['out-of-stock']],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Top Products Chart
    new Chart(document.getElementById('topProductsChart'), {
        type: 'bar',
        data: {
            labels: topProducts.map(p => p.name),
            datasets: [{
                label: 'Value (₱)',
                data: topProducts.map(p => p.quantity * p.unit_price),
                backgroundColor: '#2874a6',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: { display: false }
            }
        }
    });

    // Category Quantity Chart
    new Chart(document.getElementById('categoryQuantityChart'), {
        type: 'pie',
        data: {
            labels: Object.keys(categorySummary),
            datasets: [{
                data: Object.values(categorySummary).map(item => item.total_quantity),
                backgroundColor: ['#17365d', '#2874a6', '#5dade2', '#85c1e9', '#aed6f1'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>
@endpush
@endsection
