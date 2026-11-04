<?php
namespace App\Listeners;
use App\Events\StockChanged;
use App\Models\InventoryTransaction;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
class NotifyLowStock implements ShouldQueue { public function handle(StockChanged $event): void { $tx=InventoryTransaction::with('product')->find($event->transactionId); if(!$tx||!$tx->product)return; $product=$tx->product; $before=$tx->quantity_before ?: ($product->quantity_on_hand+$tx->quantity); $after=$tx->quantity_after ?: $product->quantity_on_hand; if($after<=$product->reorder_level && $before>$product->reorder_level){ User::query()->chunkById(100,function($users)use($product){foreach($users as $user){$user->notify(new LowStockNotification($product->name,(int)$product->quantity_on_hand,(int)$product->reorder_level));}}); } } }
