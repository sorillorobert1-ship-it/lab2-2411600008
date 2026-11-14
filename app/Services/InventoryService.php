<?php

namespace App\Services;

use App\Events\StockChanged;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function move(int $productId, ?int $userId, string $type, int $quantity, ?string $referenceDocument = null, ?string $notes = null, ?Model $reference = null): InventoryTransaction
    {
        if (! in_array($type, ['stock_in', 'stock_out'], true) || $quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'Invalid stock movement.']);
        }

        $transaction = DB::transaction(function () use ($productId, $userId, $type, $quantity, $referenceDocument, $notes, $reference) {
            $product = Product::whereKey($productId)->lockForUpdate()->firstOrFail();
            $before = (int) $product->quantity_on_hand;
            $after = $before + ($type === 'stock_in' ? $quantity : -$quantity);

            if ($after < 0) {
                throw ValidationException::withMessages(['quantity' => 'Insufficient stock available.']);
            }

            $product->update(['quantity_on_hand' => $after, 'quantity' => $after]);

            return InventoryTransaction::create([
                'product_id' => $product->id,
                'user_id' => $userId,
                'type' => $type === 'stock_in' ? 'in' : 'out',
                'transaction_type' => $type,
                'quantity' => $quantity,
                'reference_document' => $referenceDocument,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'notes' => $notes,
            ]);
        }, 3);

        StockChanged::dispatch($transaction->id);

        return $transaction;
    }
}
