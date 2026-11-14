<?php

namespace App\Exports;

class SupplierPerformanceExport extends BaseExport
{
    public function title(): string
    {
        return 'Supplier Performance Report';
    }

    public function headings(): array
    {
        return ['Supplier', 'Total Orders', 'Delivered', 'On Time', 'On-Time %', 'Quoted Lead Time', 'Avg Delivery Days', 'Accuracy %', 'Total Value', 'Score'];
    }

    public function map($metric): array
    {
        return [$metric['supplier'], $metric['total_orders'], $metric['delivered_orders'], $metric['on_time_orders'], $metric['on_time_rate'], $metric['quoted_lead_time'], $metric['average_delivery_days'] ?? '-', $metric['accuracy_rate'], number_format($metric['total_value'], 2), $metric['score']];
    }
}
