<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InventoryTransaction extends Model {
 public $timestamps=false;
 protected $fillable=['product_id','user_id','transaction_type','quantity','reference_document','quantity_before','quantity_after','notes'];
 protected $casts=['created_at'=>'datetime'];
 public function product(){return $this->belongsTo(Product::class);}
 public function user(){return $this->belongsTo(User::class);}
 public function scopeStockIn($query){return $query->where('transaction_type','stock_in');}
 public function scopeStockOut($query){return $query->where('transaction_type','stock_out');}
 public function scopeRecent($query){return $query->latest('created_at');}
}
