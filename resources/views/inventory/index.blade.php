@extends('layouts.app')

@section('title', 'Real-Time Inventory')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div><h1 class="h2 fw-bold mb-1">Real-Time Inventory</h1><p class="text-muted mb-0">Record stock movement, monitor alerts, and audit every change.</p></div>
        <span class="badge rounded-pill text-bg-primary px-3 py-2">Live updates every 30 seconds</span>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ implode(' ', $errors->all()) }}</div>@endif

    <div class="row g-4 mb-4">
        <div class="col-xl-6"><div class="card h-100 shadow-sm"><div class="card-body p-4">
            <div class="d-flex align-items-center mb-3"><span class="inventory-icon bg-primary-subtle text-primary">↕</span><div><h2 class="h5 mb-1">Record Stock Movement</h2><p class="text-muted small mb-0">Log stock in or stock out activity.</p></div></div>
            <form method="POST" action="{{ route('inventory.store') }}" class="row g-3">@csrf
                <div class="col-md-6"><label class="form-label">Product</label><select name="product_id" id="movement-product" required class="form-select"><option value="">Choose product</option>@foreach($products as $product)<option value="{{ $product->id }}" data-quantity="{{ $product->quantity }}">{{ $product->name }} ({{ $product->quantity }} available)</option>@endforeach</select><div id="movement-stock-help" class="form-text"></div></div>
                <div class="col-md-6"><label class="form-label">Movement type</label><select name="transaction_type" id="movement-type" required class="form-select"><option value="stock_in">Stock In</option><option value="stock_out">Stock Out</option></select></div>
                <div class="col-md-4"><label class="form-label">Quantity</label><input type="number" name="quantity" id="movement-quantity" min="1" placeholder="0" required class="form-control"><div id="movement-quantity-help" class="form-text"></div></div><div class="col-md-8"><label class="form-label">Reference number</label><input name="reference_document" placeholder="PO, sales order, or document" class="form-control"></div>
                <div class="col-12"><label class="form-label">Notes</label><input name="notes" placeholder="Reason or additional context" class="form-control"></div><div class="col-12"><button class="btn btn-primary px-4">Save Movement</button></div>
            </form>
        </div></div></div>
        <div class="col-xl-6"><div class="card h-100 shadow-sm"><div class="card-body p-4">
            <div class="d-flex align-items-center mb-3"><span class="inventory-icon bg-warning-subtle text-warning">✓</span><div><h2 class="h5 mb-1">Physical Stock Adjustment</h2><p class="text-muted small mb-0">Reconcile the system quantity with a physical count.</p></div></div>
            <form method="POST" action="{{ route('inventory.adjust') }}" class="row g-3">@csrf
                <div class="col-md-6"><label class="form-label">Product</label><select name="product_id" required class="form-select"><option value="">Choose product</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }} ({{ $product->quantity }} current)</option>@endforeach</select></div><div class="col-md-6"><label class="form-label">Physical count</label><input type="number" name="physical_quantity" min="0" placeholder="0" required class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Reason</label><input name="reason" placeholder="Damage, loss, recount" required class="form-control"></div><div class="col-md-6"><label class="form-label">Notes</label><input name="notes" placeholder="Additional details" class="form-control"></div><div class="col-12"><button class="btn btn-warning px-4">Save Adjustment</button></div>
            </form>
        </div></div></div>
    </div>

    <div class="row g-3 mb-4" id="live-stats">
        <div class="col-6 col-xl-3"><div class="stat-card h-100"><div class="text-muted small">Products</div><div class="stat-number" id="total-products">—</div><div class="small text-muted">Tracked items</div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card h-100"><div class="text-muted small">Total units</div><div class="stat-number" id="total-units">—</div><div class="small text-muted">Current stock count</div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card h-100"><div class="text-muted small">Low stock</div><div class="stat-number text-warning" id="low-stock">—</div><div class="small text-muted">Needs attention</div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card h-100"><div class="text-muted small">Inventory value</div><div class="stat-number text-primary">₱<span id="inventory-value">—</span></div><div class="small text-muted">Current valuation</div></div></div>
    </div>

    <div class="card shadow-sm mb-4"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Recent Stock Movements</h2><span class="text-muted small">Audit trail</span></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Date</th><th>Product</th><th>Type</th><th>Qty</th><th>Balance</th><th>User</th><th>Reference</th></tr></thead><tbody>@foreach($transactions as $tx)<tr><td class="text-nowrap">{{ $tx->created_at }}</td><td class="fw-semibold">{{ $tx->product?->name ?? 'Deleted' }}</td><td><span class="badge {{ $tx->transaction_type === 'stock_in' ? 'text-bg-success' : ($tx->transaction_type === 'adjustment' ? 'text-bg-warning' : 'text-bg-danger') }}">{{ str_replace('_', ' ', $tx->transaction_type) }}</span></td><td>{{ $tx->transaction_type === 'stock_in' ? '+' : '-' }}{{ $tx->quantity }}</td><td>{{ $tx->quantity_before }} → {{ $tx->quantity_after }}</td><td>{{ $tx->user?->name ?? 'Unknown' }}</td><td>{{ $tx->reference_document ?: '—' }}</td></tr>@endforeach</tbody></table></div><div class="mt-3">{{ $transactions->links() }}</div></div></div>
    <div class="card shadow-sm"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-center"><h2 class="h5 mb-0">Unread Low-Stock Notifications</h2><form action="{{ route('inventory.notifications.read') }}" method="POST">@csrf<button class="btn btn-sm btn-outline-secondary">Mark all as read</button></form></div><ul id="alerts" class="mt-3 mb-0"></ul></div></div>
</div>
<div class="card shadow-sm mb-4"><div class="card-body p-4"><h2 class="h5">Filter Transactions</h2><form method="GET" class="row g-2"><div class="col-md-3"><select name="product_id" class="form-select"><option value="">All products</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name }}</option>@endforeach</select></div><div class="col-md-2"><select name="transaction_type" class="form-select"><option value="">All types</option><option value="stock_in">Stock in</option><option value="stock_out">Stock out</option><option value="adjustment">Adjustment</option></select></div><div class="col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control"></div><div class="col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control"></div><div class="col-md-2"><button class="btn btn-outline-primary">Filter</button></div><div class="col-md-1"><a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">Reset</a></div></form></div></div>
<script>
const movementProduct=document.getElementById('movement-product'), movementType=document.getElementById('movement-type'), movementQuantity=document.getElementById('movement-quantity'), movementStockHelp=document.getElementById('movement-stock-help'), movementQuantityHelp=document.getElementById('movement-quantity-help');
function updateMovementLimit(){const option=movementProduct.selectedOptions[0], current=option?.dataset.quantity; movementStockHelp.textContent=current === undefined ? '' : `Current stock: ${current}`; if(movementType.value==='stock_out' && current !== undefined){movementQuantity.max=current; movementQuantityHelp.textContent=`Stock out cannot exceed ${current}.`;}else{movementQuantity.removeAttribute('max'); movementQuantityHelp.textContent='';} if(movementQuantity.value && movementQuantity.max && Number(movementQuantity.value)>Number(movementQuantity.max)) movementQuantity.value=movementQuantity.max;}
movementProduct.addEventListener('change',updateMovementLimit); movementType.addEventListener('change',updateMovementLimit); updateMovementLimit();
async function refreshInventory(){try{const [r,n]=await Promise.all([fetch(@json(route('inventory.api'))),fetch(@json(route('inventory.notifications')))]);if(!r.ok||!n.ok)return;const d=await r.json(),a=await n.json();document.getElementById('total-products').textContent=d.total_products;document.getElementById('total-units').textContent=d.total_quantity;document.getElementById('low-stock').textContent=d.low_stock;document.getElementById('inventory-value').textContent=Number(d.inventory_value).toLocaleString('en-PH',{minimumFractionDigits:2});document.getElementById('alerts').replaceChildren(...a.map(x=>{let li=document.createElement('li');li.textContent=x.data.message;return li}));}catch(e){console.warn('Inventory refresh failed',e)}}refreshInventory();setInterval(refreshInventory,30000);
</script>
@endsection
