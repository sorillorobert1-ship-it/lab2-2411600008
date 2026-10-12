@extends('layouts.app')

@section('title', 'Edit Room')

@section('content')
<div class="mb-4">
    <h1 class="fw-bold">Edit Room</h1>
    <p class="text-muted">Update {{ $room->name }}.</p>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('rooms.update', $room) }}">
            @csrf
            @method('PUT')
            @include('rooms._form')
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Update Room</button>
                <a href="{{ route('rooms.show', $room) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
