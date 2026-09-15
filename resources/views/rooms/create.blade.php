@extends('layouts.app')

@section('title', 'Add Room')

@section('content')
<div class="mb-4">
    <h1 class="fw-bold">Add Room</h1>
    <p class="text-muted">Add a new room type to the hotel inventory.</p>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('rooms.store') }}">
            @csrf
            @include('rooms._form')
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Save Room</button>
                <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
