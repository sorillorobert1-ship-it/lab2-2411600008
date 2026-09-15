@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="mb-4">
    <h1 class="fw-bold">Edit Product</h1>
    <p class="text-muted">Update {{ $product->name }}.</p>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('products.update', $product) }}">
            @csrf
            @method('PUT')
            @include('products._form')

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Update Product</button>
                <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
