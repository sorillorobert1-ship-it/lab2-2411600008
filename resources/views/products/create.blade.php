@extends('layouts.app')

@section('title', 'Add Product')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-bold mb-1">Add Product</h1>
        <p class="text-muted mb-0">Create a new inventory item.</p>
    </div>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Back to Products</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('products.store') }}">
            @csrf

            @include('products._form', ['submitText' => 'Add Product'])
        </form>
    </div>
</div>
@endsection