<h1>Purchase order {{ $order->po_number }}</h1><p>The order status is now <strong>{{ str_replace('_',' ',ucwords($order->status)) }}</strong>.</p><p>Total: ₱{{ number_format($order->total,2) }}</p>
