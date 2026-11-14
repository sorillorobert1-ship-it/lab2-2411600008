@extends('layouts.app')
@section('title', 'Suppliers')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><h1 class="page-title mb-0">Supplier Management</h1><p class="text-muted mb-0">Vendors, lead times and payment terms for hotel supplies.</p></div>
    @can('manage-suppliers')<a class="btn btn-primary" href="{{ route('suppliers.create') }}"><i class="bi bi-plus-lg"></i> Add Supplier</a>@endcan
</div>

<x-filter-form :action="route('suppliers.index')" :fields="[
    ['name' => 'search', 'label' => 'Search', 'placeholder' => 'Name, contact person or email'],
    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive', 'archived' => 'Archived (deleted)']],
    ['name' => 'sort', 'label' => 'Sort by', 'type' => 'select', 'options' => ['name' => 'Name', 'lead_time_days' => 'Lead time', 'reliability_score' => 'Reliability', 'created_at' => 'Date added']],
    ['name' => 'direction', 'label' => 'Order', 'type' => 'select', 'options' => ['asc' => 'Ascending', 'desc' => 'Descending']],
]" />

<div class="table-responsive">
<table class="table table-hover align-middle">
    <thead><tr><th>Code</th><th>Supplier</th><th>Contact</th><th>Terms</th><th>Lead time</th><th>Min. order</th><th>Products</th><th>Open POs</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    @forelse ($suppliers as $supplier)
        <tr>
            <td><code>{{ $supplier->code }}</code></td>
            <td class="fw-semibold">{{ $supplier->name }}</td>
            <td class="small">{{ $supplier->contact_person }}<br><span class="text-muted">{{ $supplier->email }} &middot; {{ $supplier->phone }}</span></td>
            <td>{{ $supplier->payment_terms }}</td>
            <td>{{ $supplier->lead_time_days }} days</td>
            <td>₱{{ number_format($supplier->minimum_order_amount, 2) }}</td>
            <td>{{ $supplier->products_count }}</td>
            <td>{{ $supplier->pending_orders_count }}</td>
            <td>
                @if ($supplier->trashed())<span class="badge bg-dark">Archived</span>
                @elseif ($supplier->is_active)<span class="badge bg-success">Active</span>
                @else<span class="badge bg-secondary">Inactive</span>@endif
            </td>
            <td class="text-end text-nowrap">
                @if ($supplier->trashed())
                    @can('manage-suppliers')
                        <form method="POST" action="{{ route('suppliers.restore', $supplier->id) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-success">Restore</button></form>
                    @endcan
                @else
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('suppliers.show', $supplier) }}">View</a>
                    @can('manage-suppliers')
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('suppliers.edit', $supplier) }}">Edit</a>
                        <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="d-inline" onsubmit="return confirm('Archive {{ $supplier->name }}?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                    @endcan
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="10" class="text-center text-muted py-4">No suppliers found.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $suppliers->links() }}
@endsection
