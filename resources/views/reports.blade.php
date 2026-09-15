@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<!-- Print Header (visible only when printing) -->
<div class="print-header d-none d-print-block mb-4">
    <h1 class="fw-bold">Robert Hotel - Inventory Management Report</h1>
    <p class="text-muted">Generated: {{ now()->format('F j, Y - g:i A') }}</p>
    <hr>
</div>

<div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
    <div>
        <h1 class="fw-bold mb-1">Inventory Reports</h1>
        <p class="text-muted mb-0">Comprehensive inventory analytics and insights.</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Print Report
        </button>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>
</div>

<!-- SUMMARY CARDS -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="small">Total Categories</div>
            <div class="stat-number">{{ $productsByCategory->count() }}</div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="small">Total Inventory Value</div>
            <div class="stat-number">₱{{ number_format((float) $totalInventoryValue, 2) }}</div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="small">Out of Stock Items</div>
            <div class="stat-number">{{ $outOfStockProducts->count() }}</div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="small">Suppliers</div>
            <div class="stat-number">{{ $supplierSummary->count() }}</div>
        </div>
    </div>
</div>

<!-- CHARTS -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="chart-card">
            <h5>Products by Category</h5>
            <div class="chart-wrap">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="chart-card">
            <h5>Low Stock by Category</h5>
            <div class="chart-wrap">
                <canvas id="lowStockChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="chart-card">
            <h5>Monthly Inventory Value</h5>
            <div class="chart-wrap">
                <canvas id="monthlyValueChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="chart-card">
            <h5>Supplier Performance</h5>
            <div class="chart-wrap">
                <canvas id="supplierChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- DETAILED TABLES -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Products by Category</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productsByCategory as $category)
                                <tr>
                                    <td>{{ $category->category }}</td>
                                    <td class="text-end">{{ $category->count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Out of Stock Products</h5>
                @if($outOfStockProducts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Product</th>
                                    <th>Category</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($outOfStockProducts as $product)
                                    <tr>
                                        <td>{{ $product->sku }}</td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->category }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No out of stock products.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Supplier Summary</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th class="text-end">Products</th>
                                <th class="text-end">Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($supplierSummary as $supplier)
                                <tr>
                                    <td>{{ $supplier->supplier }}</td>
                                    <td class="text-end">{{ $supplier->count }}</td>
                                    <td class="text-end">₱{{ number_format((float) $supplier->total_value, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    // Products by Category Chart
    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: @json($productsByCategory->pluck('category')),
            datasets: [{
                label: 'Products',
                data: @json($productsByCategory->pluck('count')),
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

    // Low Stock by Category Chart
    new Chart(document.getElementById('lowStockChart'), {
        type: 'doughnut',
        data: {
            labels: @json($lowStockByCategory->pluck('category')),
            datasets: [{
                data: @json($lowStockByCategory->pluck('count')),
                backgroundColor: ['#dc3545', '#ffc107', '#fd7e14', '#20c997', '#6f42c1'],
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

    // Monthly Value Chart
    new Chart(document.getElementById('monthlyValueChart'), {
        type: 'line',
        data: {
            labels: @json($monthlyValue->pluck('month')),
            datasets: [{
                label: 'Inventory Value (₱)',
                data: @json($monthlyValue->pluck('total_value')),
                borderColor: '#2874a6',
                backgroundColor: 'rgba(40, 116, 166, 0.1)',
                fill: true,
                tension: 0.4
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

    // Supplier Performance Chart
    new Chart(document.getElementById('supplierChart'), {
        type: 'bar',
        data: {
            labels: @json($supplierSummary->pluck('supplier')),
            datasets: [{
                label: 'Total Value (₱)',
                data: @json($supplierSummary->pluck('total_value')),
                backgroundColor: '#17365d',
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
</script>
@endpush
@endsection