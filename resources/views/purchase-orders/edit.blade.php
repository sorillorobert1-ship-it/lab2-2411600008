@extends('layouts.app')
@section('title', 'Edit '.$order->reference_number)
@section('content')
<h1 class="page-title mb-3">Edit Draft {{ $order->reference_number }}</h1>
@include('purchase-orders.form')
@endsection
