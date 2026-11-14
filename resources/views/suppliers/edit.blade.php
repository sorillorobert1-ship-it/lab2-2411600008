@extends('layouts.app')
@section('title', 'Edit Supplier')
@section('content')
<h1 class="page-title mb-3">Edit Supplier: {{ $supplier->name }}</h1>
@include('suppliers.form')
@endsection
