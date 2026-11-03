<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;
    protected $fillable = ['code','name','contact_person','email','phone','address','lead_time_days','reliability_score','is_active'];
    protected $casts = ['is_active'=>'boolean','reliability_score'=>'decimal:2'];
    public function products() { return $this->belongsToMany(Product::class)->withPivot('supplier_sku','unit_cost','minimum_order_quantity','is_preferred')->withTimestamps(); }
    public function purchaseOrders() { return $this->hasMany(PurchaseOrder::class); }
}
