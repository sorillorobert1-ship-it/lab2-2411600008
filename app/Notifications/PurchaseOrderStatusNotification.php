<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PurchaseOrderStatusNotification extends Notification
{
    use Queueable;

    public function __construct(public PurchaseOrder $order) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'purchase_order_id' => $this->order->id,
            'reference_number' => $this->order->reference_number,
            'status' => $this->order->status,
            'message' => "Purchase order {$this->order->reference_number} is now {$this->order->status_label}.",
        ];
    }
}
