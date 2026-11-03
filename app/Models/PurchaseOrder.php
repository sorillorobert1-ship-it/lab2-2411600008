<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = ['po_number','supplier_id','created_by','approved_by','status','expected_delivery_date','ordered_at','received_at','subtotal','tax','total','notes'];
    protected $casts = ['expected_delivery_date'=>'date','ordered_at'=>'date','received_at'=>'date','subtotal'=>'decimal:2','tax'=>'decimal:2','total'=>'decimal:2'];
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function items() { return $this->hasMany(PurchaseOrderItem::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function recalculateTotals(): void { $this->subtotal=$this->items()->sum('line_total'); $this->tax=round($this->subtotal*0.12,2); $this->total=$this->subtotal+$this->tax; $this->save(); }
}
