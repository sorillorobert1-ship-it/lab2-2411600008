@extends('emails.layout')
@section('body')
<h2 style="color:#17365d;margin-top:0;">Purchase Order {{ $order->reference_number }}</h2>
<p>Dear {{ $order->supplier->contact_person ?? $order->supplier->name }},</p>
<p>Please find below (and attached as PDF) our purchase order. Kindly deliver on or before <strong>{{ $order->expected_delivery_date?->format('F d, Y') ?? 'the agreed date' }}</strong>.</p>
@include('emails.partials.items')
<p><strong>Payment terms:</strong> {{ $order->terms ?? $order->supplier->payment_terms }}</p>
@if ($order->notes)<p><strong>Notes:</strong> {{ $order->notes }}</p>@endif
<p>Thank you,<br>Robert Hotel Purchasing</p>
@endsection
