<?php
namespace App\Listeners;
use App\Events\StockChanged;
use App\Models\InventoryTransaction;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
class NotifyLowStock implements ShouldQueue {
 public function handle(StockChanged $event):void {
  $tx=InventoryTransaction::with('product')->find($event->transactionId);
  if(!$tx||!$tx->product)return;
  $product=$tx->product;
  if($tx->quantity_after<=$product->reorder_level && $tx->quantity_before>$product->reorder_level){
   User::query()->chunkById(100,function($users)use($product){
    foreach($users as $user){
     /** @var User $user */
     $user->notify(new LowStockNotification($product->name,$product->quantity,$product->reorder_level));
    }
   });
  }
 }
}
