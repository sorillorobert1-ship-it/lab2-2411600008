@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1">Edit Product</h1>
        <p class="text-muted mb-0">Update product information.</p>
    </div>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Back to Products</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('products.update', $product) }}">
            @csrf
            @method('PUT')

            @include('products._form', ['submitText' => 'Update Product'])
        </form>
    </div>
</div>
@endsection