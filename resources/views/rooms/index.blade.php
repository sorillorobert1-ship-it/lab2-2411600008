@extends('layouts.app')

@section('title', 'Rooms')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="fw-bold mb-1">Rooms</h1>
        <p class="text-muted mb-0">Manage room inventory for Robert Hotel reservations.</p>
    </div>
    <a href="{{ route('rooms.create') }}" class="btn btn-primary">+ Add Room</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Room Code</th>
                    <th>Type</th>
                    <th>Available</th>
                    <th>Alert Level</th>
                    <th>Nightly Rate</th>
                    <th>Wing / Floor</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($rooms as $room)
                <tr>
                    <td class="fw-semibold">{{ $room->name }}</td>
                    <td>{{ $room->sku }}</td>
                    <td>{{ $room->category }}</td>
                    <td>
                        @if($room->isOutOfStock())
                            <span class="badge bg-danger">0</span>
                        @elseif($room->isLowStock())
                            <span class="badge bg-warning text-dark">{{ $room->quantity }} Low</span>
                        @else
                            <span class="badge bg-success">{{ $room->quantity }}</span>
                        @endif
                    </td>
                    <td>{{ $room->reorder_level }}</td>
                    <td>₱{{ number_format((float) $room->unit_price, 2) }}</td>
                    <td>{{ $room->supplier ?: '—' }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('rooms.show', $room) }}" class="btn btn-sm btn-outline-secondary">View</a>
                        <a href="{{ route('rooms.edit', $room) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('rooms.destroy', $room) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this room?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">No rooms found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($rooms->hasPages())
        <div class="card-footer bg-white">
            {{ $rooms->links() }}
        </div>
    @endif
</div>
@endsection
