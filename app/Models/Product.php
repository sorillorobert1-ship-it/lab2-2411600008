<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'sku', 'name', 'description', 'category', 'unit', 'quantity_on_hand', 'quantity',
        'reorder_level', 'safety_stock', 'unit_cost', 'unit_price', 'supplier_id', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
        'quantity_on_hand' => 'integer',
        'reorder_level' => 'integer',
        'safety_stock' => 'integer',
    ];

    /** Primary (preferred) supplier used for automatic reordering. */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /** All suppliers that can deliver this product, with agreed pricing. */
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class)
            ->withPivot('supplier_sku', 'unit_cost', 'minimum_order_quantity', 'is_preferred')
            ->withTimestamps();
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function transactions()
    {
        return $this->inventoryTransactions();
    }

    public function latestTransaction()
    {
        return $this->hasOne(InventoryTransaction::class)->latestOfMany();
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity_on_hand <= $this->reorder_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity_on_hand <= 0;
    }

    public function getStockStatusAttribute(): string
    {
        return $this->isOutOfStock() ? 'Out of Stock' : ($this->isLowStock() ? 'Low Stock' : 'In Stock');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('quantity_on_hand', '<=', 'reorder_level');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%");
            }))
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->where('category', $category))
            ->when($filters['supplier_id'] ?? null, fn ($q, $id) => $q->where('supplier_id', $id))
            ->when(($filters['stock'] ?? null) === 'low', fn ($q) => $q->lowStock());
    }
}
