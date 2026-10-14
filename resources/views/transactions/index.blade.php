@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h1 class="h2 fw-bold mb-1">Inventory Transactions</h1><p class="text-muted mb-0">Complete stock movement audit trail.</p></div>
    <a href="{{ route('transactions.create') }}" class="btn btn-primary">Record Transaction</a>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="card shadow-sm"><div class="card-body p-4">
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-3"><select name="product_id" class="form-select"><option value="">All products</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><select name="transaction_type" class="form-select"><option value="">All types</option><option value="stock_in">Stock in</option><option value="stock_out">Stock out</option><option value="adjustment">Adjustment</option></select></div>
        <div class="col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control"></div><div class="col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control"></div><div class="col-md-2"><button class="btn btn-outline-primary">Filter</button></div>
    </form>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead class="table-light"><tr><th>Date/Time</th><th>Product</th><th>Type</th><th>Quantity</th><th>Balance</th><th>Reference</th><th>User</th><th></th></tr></thead><tbody>
    @forelse($transactions as $transaction)<tr><td>{{ $transaction->created_at }}</td><td>{{ $transaction->product?->name ?? 'Deleted' }}</td><td>{{ str_replace('_', ' ', $transaction->transaction_type) }}</td><td>{{ $transaction->transaction_type === 'stock_in' ? '+' : '-' }}{{ $transaction->quantity }}</td><td>{{ $transaction->quantity_before }} → {{ $transaction->quantity_after }}</td><td>{{ $transaction->reference_document ?: '—' }}</td><td>{{ $transaction->user?->name ?? 'Unknown' }}</td><td><a href="{{ route('transactions.show', $transaction) }}" class="btn btn-sm btn-outline-primary">View</a></td></tr>@empty<tr><td colspan="8" class="text-muted">No transactions found.</td></tr>@endforelse
    </tbody></table></div>{{ $transactions->links() }}
</div></div>
@endsection
