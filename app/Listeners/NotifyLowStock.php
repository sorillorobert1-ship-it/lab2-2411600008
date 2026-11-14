<?php

namespace App\Listeners;

use App\Events\StockChanged;
use App\Mail\LowStockAlert;
use App\Models\InventoryTransaction;
use App\Models\User;
use App\Notifications\LowStockNotification;
use App\Providers\ViewServiceProvider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class NotifyLowStock implements ShouldQueue
{
    public function handle(StockChanged $event): void
    {
        ViewServiceProvider::forgetWidgetCache();

        $transaction = InventoryTransaction::with('product')->find($event->transactionId);

        if (! $transaction?->product) {
            return;
        }

        $product = $transaction->product;
        $crossedThreshold = $transaction->quantity_after <= $product->reorder_level
            && $transaction->quantity_before > $product->reorder_level;

        if (! $crossedThreshold) {
            return;
        }

        $recipients = User::whereIn('role', ['admin', 'manager'])->get();

        foreach ($recipients as $user) {
            $user->notify(new LowStockNotification($product->name, $product->quantity_on_hand, $product->reorder_level));
        }

        try {
            if ($recipients->isNotEmpty()) {
                Mail::to($recipients)->send(new LowStockAlert(collect([$product])));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
