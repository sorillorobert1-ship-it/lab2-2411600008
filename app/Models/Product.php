<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['sku','name','description','category','unit','quantity_on_hand','quantity','reorder_level','safety_stock','unit_cost','unit_price','supplier','is_active'];
    protected $casts = ['is_active'=>'boolean','unit_cost'=>'decimal:2','unit_price'=>'decimal:2','quantity'=>'integer','quantity_on_hand'=>'integer'];
    public function suppliers() { return $this->belongsToMany(Supplier::class)->withPivot('supplier_sku','unit_cost','minimum_order_quantity','is_preferred')->withTimestamps(); }
    public function inventoryTransactions() { return $this->hasMany(InventoryTransaction::class); }
    public function purchaseOrderItems() { return $this->morphMany(PurchaseOrderItem::class, 'purchasable'); }
    public function transactions() { return $this->hasMany(InventoryTransaction::class); }
    public function latestTransaction() { return $this->hasOne(InventoryTransaction::class)->latestOfMany(); }
    public function isLowStock(): bool { return $this->quantity_on_hand <= $this->reorder_level; }
    public function isOutOfStock(): bool { return $this->quantity_on_hand <= 0; }
    public function getStockStatusAttribute(): string { return $this->isOutOfStock() ? 'Out of Stock' : ($this->isLowStock() ? 'Low Stock' : 'In Stock'); }
    public function scopeLowStock($query) { return $query->whereColumn('quantity_on_hand', '<=', 'reorder_level'); }
}
