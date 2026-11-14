@extends('emails.layout')
@section('body')
<h2 style="color:#c0392b;margin-top:0;">Low stock alert</h2>
<p>The following {{ $products->count() }} product(s) are at or below their reorder point and need replenishment:</p>
<table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:14px;margin:16px 0;">
    <tr style="background:#c0392b;color:#ffffff;"><th align="left">Product</th><th align="left">Supplier</th><th align="right">On hand</th><th align="right">Reorder level</th></tr>
    @foreach ($products as $product)
        <tr style="border-bottom:1px solid #e9ecef;"><td>{{ $product->name }}<br><span style="color:#6c757d;font-size:12px;">{{ $product->sku }}</span></td><td>{{ $product->supplier?->name ?? '—' }}</td><td align="right" style="color:#c0392b;font-weight:bold;">{{ $product->quantity_on_hand }} {{ $product->unit }}</td><td align="right">{{ $product->reorder_level }}</td></tr>
    @endforeach
</table>
<p style="text-align:center;margin:24px 0;"><a href="{{ route('dashboard') }}" style="background:#f4b400;color:#17365d;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;">Open Procurement Dashboard</a></p>
@endsection
