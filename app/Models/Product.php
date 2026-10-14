<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'category',
        'quantity',
        'reorder_level',
        'unit_price',
        'supplier',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reorder_level' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function transactions(){return $this->hasMany(InventoryTransaction::class);}
    public function latestTransaction(){return $this->hasOne(InventoryTransaction::class)->latestOfMany();}
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    public function getStockStatusAttribute(): string
    {
        return $this->isOutOfStock() ? 'Out of Stock' : ($this->isLowStock() ? 'Low Stock' : 'In Stock');
    }

    public function getStockStatusColorAttribute(): string
    {
        return $this->isOutOfStock() ? 'danger' : ($this->isLowStock() ? 'warning' : 'success');
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'reorder_level');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
