@extends('layouts.app')

@section('title', 'Add Product')

@section('content')
<div class="mb-4">
    <h1 class="fw-bold">Add Product</h1>
    <p class="text-muted">Add a new construction material to the inventory.</p>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('products.store') }}">
            @csrf
            @include('products._form')

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Save Product</button>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
