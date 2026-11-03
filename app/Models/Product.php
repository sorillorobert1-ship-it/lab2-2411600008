<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['sku','name','unit','quantity_on_hand','reorder_level','safety_stock','unit_cost','is_active'];
    protected $casts = ['is_active'=>'boolean','unit_cost'=>'decimal:2'];
    public function suppliers() { return $this->belongsToMany(Supplier::class)->withPivot('supplier_sku','unit_cost','minimum_order_quantity','is_preferred')->withTimestamps(); }
    public function inventoryTransactions() { return $this->hasMany(InventoryTransaction::class); }
    public function purchaseOrderItems() { return $this->morphMany(PurchaseOrderItem::class, 'purchasable'); }
}
