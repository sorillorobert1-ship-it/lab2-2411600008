<?php

namespace App\Exports;

class InventoryTransactionsExport extends BaseExport
{
    public function title(): string
    {
        return 'Inventory Transactions';
    }

    public function headings(): array
    {
        return ['Date', 'Product', 'Type', 'Quantity', 'Before', 'After', 'Reference', 'User'];
    }

    public function map($transaction): array
    {
        return [$transaction->created_at?->format('Y-m-d H:i'), $transaction->product?->name, strtoupper($transaction->type), $transaction->quantity, $transaction->quantity_before, $transaction->quantity_after, $transaction->reference_document, $transaction->user?->name ?? 'System'];
    }
}
