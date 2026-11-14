@extends('layouts.app')
@section('title', $order->reference_number)
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="page-title mb-0">{{ $order->reference_number }} <x-status-badge :status="$order->status" class="fs-6 align-middle" /></h1>
        <p class="text-muted mb-0">{{ $order->supplier->name }} @if ($order->supplier->trashed())<span class="badge bg-dark">Archived supplier</span>@endif</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-danger" href="{{ route('purchase-orders.pdf', $order) }}"><i class="bi bi-filetype-pdf"></i> PDF</a>
        @can('update', $order)<a class="btn btn-outline-primary" href="{{ route('purchase-orders.edit', $order) }}">Edit</a>@endcan
        @can('submit', $order)<form method="POST" action="{{ route('purchase-orders.submit', $order) }}">@csrf<button class="btn btn-primary">Submit for Approval</button></form>@endcan
        @can('approve', $order)<form method="POST" action="{{ route('purchase-orders.approve', $order) }}">@csrf<button class="btn btn-success"><i class="bi bi-check-lg"></i> Approve</button></form>@endcan
        @can('reject', $order)<button class="btn btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#reject-box"><i class="bi bi-x-lg"></i> Reject</button>@endcan
        @can('send', $order)<form method="POST" action="{{ route('purchase-orders.send', $order) }}">@csrf<button class="btn btn-info"><i class="bi bi-send"></i> Send to Supplier</button></form>@endcan
        @can('delete', $order)<form method="POST" action="{{ route('purchase-orders.destroy', $order) }}" onsubmit="return confirm('Delete this draft?')">@csrf @method('DELETE')<button class="btn btn-outline-danger">Delete Draft</button></form>@endcan
        @can('cancel', $order)<form method="POST" action="{{ route('purchase-orders.destroy', $order) }}" onsubmit="return confirm('Cancel this purchase order?')">@csrf @method('DELETE')<button class="btn btn-outline-dark">Cancel Order</button></form>@endcan
        <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.index') }}">Back</a>
    </div>
</div>

@can('reject', $order)
<div class="collapse mb-3" id="reject-box">
    <form method="POST" action="{{ route('purchase-orders.reject', $order) }}" class="filter-card">
        @csrf
        <label class="form-label">Rejection reason *</label>
        <textarea name="rejection_reason" class="form-control mb-2" rows="2" required></textarea>
        <button class="btn btn-danger btn-sm">Confirm Rejection</button>
    </form>
</div>
@endcan

@if ($order->status === 'rejected' && $order->rejection_reason)
    <div class="alert alert-danger"><strong>Rejected:</strong> {{ $order->rejection_reason }}</div>
@endif

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card stat-card p-3"><span class="small text-muted">Order date</span><strong>{{ $order->order_date->format('M d, Y') }}</strong></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><span class="small text-muted">Expected delivery</span><strong>{{ $order->expected_delivery_date?->format('M d, Y') ?? '—' }}</strong></div></div>
    <div class="col-md-3"><div class="card stat-card accent p-3"><span class="small text-muted">Created by</span><strong>{{ $order->createdBy?->name ?? 'System (auto reorder)' }}</strong></div></div>
    <div class="col-md-3"><div class="card stat-card warning p-3"><span class="small text-muted">Approved by</span><strong>{{ $order->approvedBy?->name ?? '—' }}</strong>@if ($order->approved_at)<span class="small text-muted">{{ $order->approved_at->format('M d, Y h:i A') }}</span>@endif</div></div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        @php $canReceive = auth()->user()->can('receive', $order); @endphp
        @if ($canReceive)<form method="POST" action="{{ route('purchase-orders.receive', $order) }}">@csrf @endif
        <div class="card widget-card h-auto">
            <div class="card-header">Line items</div>
            <div class="card-body p-0">
                <table class="table mb-0 align-middle">
                    <thead><tr><th>Product</th><th class="text-end">Ordered</th><th class="text-end">Received</th><th class="text-end">Outstanding</th><th class="text-end">Unit price</th><th class="text-end">Line total</th>@if ($canReceive)<th>Receive now</th>@endif</tr></thead>
                    <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}<br><small class="text-muted">{{ $item->product->sku }}</small></td>
                            <td class="text-end">{{ $item->quantity }} {{ $item->product->unit }}</td>
                            <td class="text-end">{{ $item->received_quantity }}</td>
                            <td class="text-end">{{ $item->outstanding_quantity }}</td>
                            <td class="text-end">₱{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end">₱{{ number_format($item->line_total, 2) }}</td>
                            @if ($canReceive)<td style="width:120px"><input type="number" min="0" max="{{ $item->outstanding_quantity }}" name="received[{{ $item->id }}]" value="{{ $item->outstanding_quantity }}" class="form-control form-control-sm"></td>@endif
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr><th colspan="5" class="text-end">Subtotal</th><th class="text-end">₱{{ number_format($order->subtotal, 2) }}</th>@if ($canReceive)<th></th>@endif</tr>
                        <tr><th colspan="5" class="text-end">VAT (12%)</th><th class="text-end">₱{{ number_format($order->tax, 2) }}</th>@if ($canReceive)<th></th>@endif</tr>
                        <tr><th colspan="5" class="text-end">Total</th><th class="text-end">₱{{ number_format($order->total, 2) }}</th>@if ($canReceive)<th></th>@endif</tr>
                    </tfoot>
                </table>
            </div>
            @if ($canReceive)<div class="card-footer bg-white text-end"><button class="btn btn-success"><i class="bi bi-box-seam"></i> Record Delivery</button></div>@endif
        </div>
        @if ($canReceive)</form>@endif

        @if ($order->notes || $order->terms)
            <div class="card widget-card h-auto mt-3"><div class="card-body small">
                @if ($order->notes)<p class="mb-1"><strong>Notes:</strong> {{ $order->notes }}</p>@endif
                @if ($order->terms)<p class="mb-0"><strong>Terms:</strong> {{ $order->terms }}</p>@endif
            </div></div>
        @endif
    </div>
    <div class="col-lg-4">
        <div class="card widget-card h-auto">
            <div class="card-header">Approval history</div>
            <ul class="list-group list-group-flush">
                @forelse ($order->histories as $history)
                    <li class="list-group-item small">
                        <div class="d-flex justify-content-between">
                            <span>@if ($history->from_status)<x-status-badge :status="$history->from_status" /> &rarr; @endif<x-status-badge :status="$history->to_status" /></span>
                            <span class="text-muted">{{ $history->created_at->format('M d, h:i A') }}</span>
                        </div>
                        <div class="text-muted">by {{ $history->user?->name ?? 'System' }}</div>
                        @if ($history->remarks)<div>{{ $history->remarks }}</div>@endif
                    </li>
                @empty
                    <li class="list-group-item small text-muted">No history yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
