<?php
namespace App\Services;
use App\Models\Product;
use App\Models\InventoryTransaction;
use App\Events\StockChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class InventoryService {
 public function move(int $productId,int $userId,string $type,int $quantity,?string $reference=null,?string $notes=null):InventoryTransaction {
  if(!in_array($type,['stock_in','stock_out'],true)||$quantity<1)throw ValidationException::withMessages(['quantity'=>'Invalid stock movement.']);
  $transaction=DB::transaction(function()use($productId,$userId,$type,$quantity,$reference,$notes){
   $product=Product::whereKey($productId)->lockForUpdate()->firstOrFail();
   $before=(int)$product->quantity;$after=$before+($type==='stock_in'?$quantity:-$quantity);
   if($after<0)throw ValidationException::withMessages(['quantity'=>'Insufficient stock available.']);
   // MySQL uses the database trigger created by the inventory migration.
   // SQLite test environments update the product here because SQLite skips that trigger migration.
   if(DB::connection()->getDriverName()!=='mysql'){
    $product->quantity=$after;
    $product->save();
   }
   return InventoryTransaction::create(['product_id'=>$product->id,'user_id'=>$userId,'transaction_type'=>$type,'quantity'=>$quantity,'reference_document'=>$reference,'quantity_before'=>$before,'quantity_after'=>$after,'notes'=>$notes]);
  },3);
  StockChanged::dispatch($transaction->id);
  return $transaction;
 }
 public function adjust(int $productId,int $userId,int $physicalQuantity,string $reason,?string $notes=null):InventoryTransaction {
  $transaction=DB::transaction(function()use($productId,$userId,$physicalQuantity,$reason,$notes){
   $product=Product::whereKey($productId)->lockForUpdate()->firstOrFail();
   $before=(int)$product->quantity;$difference=$physicalQuantity-$before;
   if($difference===0)throw ValidationException::withMessages(['physical_quantity'=>'The physical count matches the current quantity.']);
   if(DB::connection()->getDriverName()!=='mysql'){
    $product->quantity=$physicalQuantity;
    $product->save();
   }
   return InventoryTransaction::create(['product_id'=>$product->id,'user_id'=>$userId,'transaction_type'=>'adjustment','quantity'=>abs($difference),'reference_document'=>'ADJUSTMENT','quantity_before'=>$before,'quantity_after'=>$physicalQuantity,'notes'=>$reason.($notes ? ': '.$notes : '')]);
  },3);
  StockChanged::dispatch($transaction->id);
  return $transaction;
 }
}
