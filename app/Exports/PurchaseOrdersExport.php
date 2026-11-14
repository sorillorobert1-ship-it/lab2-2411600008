<?php

namespace App\Exports;

class PurchaseOrdersExport extends BaseExport
{
    public function title(): string
    {
        return 'Purchase Orders';
    }

    public function headings(): array
    {
        return ['Reference', 'Supplier', 'Status', 'Order Date', 'Expected Delivery', 'Items', 'Total', 'Created By'];
    }

    public function map($order): array
    {
        return [$order->reference_number, $order->supplier?->name, $order->status_label, $order->order_date?->format('Y-m-d'), $order->expected_delivery_date?->format('Y-m-d'), $order->items_count, number_format($order->total, 2), $order->createdBy?->name ?? 'System'];
    }
}
