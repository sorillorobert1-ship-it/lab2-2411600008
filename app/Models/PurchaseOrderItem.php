<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = ['purchase_order_id','purchasable_type','purchasable_id','quantity','received_quantity','unit_cost','line_total'];
    protected $casts = ['unit_cost'=>'decimal:2','line_total'=>'decimal:2'];
    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
    public function purchasable() { return $this->morphTo(); }
}
