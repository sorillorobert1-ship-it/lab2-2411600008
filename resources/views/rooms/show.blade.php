@extends('layouts.app')

@section('title', $room->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="fw-bold mb-1">{{ $room->name }}</h1>
        <p class="text-muted mb-0">Room code: {{ $room->sku }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rooms.edit', $room) }}" class="btn btn-primary">Edit</a>
        <form action="{{ route('rooms.destroy', $room) }}" method="POST"
              onsubmit="return confirm('Delete this room?');">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger">Delete</button>
        </form>
        <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Room Information</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Room Type</dt>
                    <dd class="col-sm-8">{{ $room->category }}</dd>
                    <dt class="col-sm-4">Description</dt>
                    <dd class="col-sm-8">{{ $room->description ?: '—' }}</dd>
                    <dt class="col-sm-4">Available</dt>
                    <dd class="col-sm-8">
                        @if($room->isOutOfStock())
                            <span class="badge bg-danger">Fully booked</span>
                        @elseif($room->isLowStock())
                            <span class="badge bg-warning text-dark">Low availability: {{ $room->quantity }}</span>
                        @else
                            <span class="badge bg-success">{{ $room->quantity }}</span>
                        @endif
                    </dd>
                    <dt class="col-sm-4">Alert Level</dt>
                    <dd class="col-sm-8">{{ $room->reorder_level }}</dd>
                    <dt class="col-sm-4">Nightly Rate</dt>
                    <dd class="col-sm-8">₱{{ number_format((float) $room->unit_price, 2) }}</dd>
                    <dt class="col-sm-4">Inventory Value</dt>
                    <dd class="col-sm-8">₱{{ number_format($room->stockValue(), 2) }}</dd>
                    <dt class="col-sm-4">Wing / Floor</dt>
                    <dd class="col-sm-8">{{ $room->supplier ?: '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Recent Reservations</h5>
            </div>
            <div class="card-body">
                @forelse($room->reservations as $transaction)
                    <div class="border-bottom py-2">
                        <div class="d-flex justify-content-between">
                            <strong>{{ strtoupper($transaction->transaction_type) }}</strong>
                            <span>{{ $transaction->quantity }}</span>
                        </div>
                        <small class="text-muted">
                            {{ $transaction->created_at?->format('M d, Y h:i A') }}
                            @if($transaction->user)
                                · {{ $transaction->user->name }}
                            @endif
                            @if($transaction->reference_document)
                                · Ref: {{ $transaction->reference_document }}
                            @endif
                        </small>
                    </div>
                @empty
                    <p class="text-muted mb-0">No reservation movements recorded yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
