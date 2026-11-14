@extends('emails.layout')
@section('body')
<h2 style="color:#17365d;margin-top:0;">New purchase order awaiting your approval</h2>
<p>Purchase order <strong>{{ $order->reference_number }}</strong> for <strong>{{ $order->supplier->name }}</strong> was submitted by {{ $order->createdBy?->name ?? 'the automated reorder check' }} and needs approval.</p>
<p style="font-size:14px;color:#6c757d;">Order date: {{ $order->order_date->format('M d, Y') }} &middot; Expected delivery: {{ $order->expected_delivery_date?->format('M d, Y') ?? 'TBD' }}</p>
@include('emails.partials.items')
<p style="text-align:center;margin:24px 0;"><a href="{{ route('purchase-orders.show', $order) }}" style="background:#f4b400;color:#17365d;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;">Review Purchase Order</a></p>
<p style="font-size:13px;color:#6c757d;">A PDF copy of the order is attached.</p>
@endsection
