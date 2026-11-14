<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderHistory extends Model
{
    protected $fillable = ['purchase_order_id', 'user_id', 'from_status', 'to_status', 'remarks'];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
