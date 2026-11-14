<table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:14px;margin:16px 0;">
    <tr style="background:#17365d;color:#ffffff;"><th align="left">Product</th><th align="right">Qty</th><th align="right">Unit price</th><th align="right">Line total</th></tr>
    @foreach ($order->items as $item)
        <tr style="border-bottom:1px solid #e9ecef;"><td>{{ $item->product->name }}<br><span style="color:#6c757d;font-size:12px;">{{ $item->product->sku }}</span></td><td align="right">{{ $item->quantity }} {{ $item->product->unit }}</td><td align="right">&#8369;{{ number_format($item->unit_price, 2) }}</td><td align="right">&#8369;{{ number_format($item->line_total, 2) }}</td></tr>
    @endforeach
    <tr><td colspan="3" align="right">Subtotal</td><td align="right">&#8369;{{ number_format($order->subtotal, 2) }}</td></tr>
    <tr><td colspan="3" align="right">VAT (12%)</td><td align="right">&#8369;{{ number_format($order->tax, 2) }}</td></tr>
    <tr><td colspan="3" align="right"><strong>Total</strong></td><td align="right" style="color:#17365d;"><strong>&#8369;{{ number_format($order->total, 2) }}</strong></td></tr>
</table>
