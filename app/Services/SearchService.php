<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Builder;

class SearchService
{
    public function products(array $filters): Builder
    {
        return Product::with('supplier')->filter($filters)->orderBy('name');
    }

    public function purchaseOrders(array $filters): Builder
    {
        return PurchaseOrder::with(['supplier', 'createdBy'])->withCount('items')->filter($filters)->latest('order_date')->latest('id');
    }

    public function transactions(array $filters): Builder
    {
        return InventoryTransaction::with(['product', 'user'])->filter($filters)->latest('created_at')->latest('id');
    }
}
