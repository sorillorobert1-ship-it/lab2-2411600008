<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #212529; }
        .header { background: #17365d; color: #fff; padding: 14px 18px; }
        .header h1 { margin: 0; font-size: 20px; }
        .accent { color: #f4b400; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th { background: #17365d; color: #fff; padding: 6px; text-align: left; }
        td { padding: 6px; border-bottom: 1px solid #dee2e6; }
        .right { text-align: right; }
        .meta td { border: 0; padding: 3px 0; }
    </style>
</head>
<body>
    <div class="header"><h1>Robert Hotel</h1><div class="accent">PURCHASE ORDER {{ $order->reference_number }}</div></div>
    <table class="meta">
        <tr><td><strong>Supplier:</strong> {{ $order->supplier->name }}</td><td><strong>Order date:</strong> {{ $order->order_date->format('M d, Y') }}</td></tr>
        <tr><td><strong>Contact:</strong> {{ $order->supplier->full_contact }}</td><td><strong>Expected delivery:</strong> {{ $order->expected_delivery_date?->format('M d, Y') ?? 'TBD' }}</td></tr>
        <tr><td><strong>Address:</strong> {{ $order->supplier->address }}</td><td><strong>Status:</strong> {{ $order->status_label }}</td></tr>
        <tr><td><strong>Payment terms:</strong> {{ $order->terms ?? $order->supplier->payment_terms }}</td><td><strong>Approved by:</strong> {{ $order->approvedBy?->name ?? '—' }}</td></tr>
    </table>
    <table>
        <tr><th>SKU</th><th>Product</th><th class="right">Quantity</th><th class="right">Unit price</th><th class="right">Line total</th></tr>
        @foreach ($order->items as $item)
            <tr><td>{{ $item->product->sku }}</td><td>{{ $item->product->name }}</td><td class="right">{{ $item->quantity }} {{ $item->product->unit }}</td><td class="right">PHP {{ number_format($item->unit_price, 2) }}</td><td class="right">PHP {{ number_format($item->line_total, 2) }}</td></tr>
        @endforeach
        <tr><td colspan="4" class="right">Subtotal</td><td class="right">PHP {{ number_format($order->subtotal, 2) }}</td></tr>
        <tr><td colspan="4" class="right">VAT (12%)</td><td class="right">PHP {{ number_format($order->tax, 2) }}</td></tr>
        <tr><td colspan="4" class="right"><strong>Total</strong></td><td class="right"><strong>PHP {{ number_format($order->total, 2) }}</strong></td></tr>
    </table>
    @if ($order->notes)<p><strong>Notes:</strong> {{ $order->notes }}</p>@endif
</body>
</html>
