@extends('emails.layout')
@section('body')
<h2 style="color:#198754;margin-top:0;">Purchase order approved</h2>
<p>Good news! Your purchase order <strong>{{ $order->reference_number }}</strong> for <strong>{{ $order->supplier->name }}</strong> was approved by <strong>{{ $order->approvedBy?->name ?? 'a manager' }}</strong> on {{ $order->approved_at?->format('M d, Y h:i A') }}.</p>
@include('emails.partials.items')
<p>The order can now be sent to the supplier.</p>
<p style="text-align:center;margin:24px 0;"><a href="{{ route('purchase-orders.show', $order) }}" style="background:#17365d;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;">View Purchase Order</a></p>
@endsection
