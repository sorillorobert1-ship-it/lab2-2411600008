<?php

namespace App\Exports;

class ProductsExport extends BaseExport
{
    public function title(): string
    {
        return 'Products List';
    }

    public function headings(): array
    {
        return ['SKU', 'Product', 'Category', 'Supplier', 'On Hand', 'Unit', 'Reorder Level', 'Unit Cost', 'Status'];
    }

    public function map($product): array
    {
        return [$product->sku, $product->name, $product->category, $product->supplier?->name, $product->quantity_on_hand, $product->unit, $product->reorder_level, number_format($product->unit_cost, 2), $product->stock_status];
    }
}
