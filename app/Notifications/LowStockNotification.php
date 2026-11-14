<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(public string $productName, public int $quantity, public int $reorderLevel) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'product' => $this->productName,
            'quantity' => $this->quantity,
            'reorder_level' => $this->reorderLevel,
            'message' => "{$this->productName} is low on stock ({$this->quantity} left, reorder level {$this->reorderLevel}).",
        ];
    }
}
