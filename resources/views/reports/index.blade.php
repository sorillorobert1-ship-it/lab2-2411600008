@extends('layouts.app')

@section('title', 'Reservation Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1">Reservation Report</h1>
        <p class="text-muted mb-0">Room valuation and low-availability items from the database.</p>
    </div>
    <button class="btn btn-outline-primary" onclick="window.print()">Print</button>
</div>

<div class="alert alert-info">
    Total available inventory value: <strong>₱{{ number_format((float) $inventoryValue, 2) }}</strong>
    · {{ $rooms->count() }} room types
    · {{ $lowStock->count() }} types need attention
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Low Availability / Alert List</h5>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Room</th>
                    <th>Code</th>
                    <th>Available</th>
                    <th>Alert Level</th>
                    <th>Wing / Floor</th>
                </tr>
            </thead>
            <tbody>
            @forelse($lowStock as $room)
                <tr>
                    <td>{{ $room->name }}</td>
                    <td>{{ $room->sku }}</td>
                    <td>
                        @if($room->isOutOfStock())
                            <span class="badge bg-danger">0</span>
                        @else
                            <span class="badge bg-warning text-dark">{{ $room->quantity }}</span>
                        @endif
                    </td>
                    <td>{{ $room->reorder_level }}</td>
                    <td>{{ $room->supplier ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center py-4 text-muted">No low-availability rooms.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Full Room Inventory Valuation</h5>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Room</th>
                    <th>Available</th>
                    <th>Nightly Rate</th>
                    <th>Inventory Value</th>
                </tr>
            </thead>
            <tbody>
            @foreach($rooms as $room)
                <tr>
                    <td>{{ $room->category }}</td>
                    <td>{{ $room->name }}</td>
                    <td>{{ $room->quantity }}</td>
                    <td>₱{{ number_format((float) $room->unit_price, 2) }}</td>
                    <td>₱{{ number_format($room->stockValue(), 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
