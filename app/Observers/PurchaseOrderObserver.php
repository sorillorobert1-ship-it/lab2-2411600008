<?php

namespace App\Observers;

use App\Mail\PurchaseOrderApproved;
use App\Mail\PurchaseOrderCreated;
use App\Mail\PurchaseOrderSent;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Notifications\PurchaseOrderStatusNotification;
use App\Providers\ViewServiceProvider;
use Illuminate\Support\Facades\Mail;

class PurchaseOrderObserver
{
    public function created(PurchaseOrder $order): void
    {
        $order->histories()->create([
            'user_id' => auth()->id() ?? $order->created_by,
            'from_status' => null,
            'to_status' => $order->status,
            'remarks' => $order->is_auto_generated ? 'Generated automatically by reorder check' : 'Purchase order created',
        ]);

        ViewServiceProvider::forgetWidgetCache();

        if ($order->status === 'pending') {
            $this->sendStatusEmails($order);
        }
    }

    public function updated(PurchaseOrder $order): void
    {
        ViewServiceProvider::forgetWidgetCache();

        if (! $order->wasChanged('status')) {
            return;
        }

        $order->histories()->create([
            'user_id' => auth()->id(),
            'from_status' => $order->getOriginal('status'),
            'to_status' => $order->status,
            'remarks' => $order->status === 'rejected' ? $order->rejection_reason : null,
        ]);

        $this->sendStatusEmails($order);
    }

    public function deleted(PurchaseOrder $order): void
    {
        ViewServiceProvider::forgetWidgetCache();
    }

    private function sendStatusEmails(PurchaseOrder $order): void
    {
        try {
            match ($order->status) {
                'pending' => $this->mailApprovers($order),
                'approved' => $this->notifyCreator($order, new PurchaseOrderApproved($order)),
                'rejected', 'received', 'partially_received', 'cancelled' => $this->notifyCreator($order),
                'sent' => $order->supplier?->email ? Mail::to($order->supplier->email)->send(new PurchaseOrderSent($order)) : null,
                default => null,
            };
        } catch (\Throwable $e) {
            // A mail server problem must not block the purchase order workflow.
            report($e);
        }
    }

    private function mailApprovers(PurchaseOrder $order): void
    {
        $approvers = User::whereIn('role', ['manager', 'admin'])->get();

        if ($approvers->isNotEmpty()) {
            Mail::to($approvers)->send(new PurchaseOrderCreated($order));
        }
    }

    private function notifyCreator(PurchaseOrder $order, $mailable = null): void
    {
        $creator = $order->createdBy;

        if (! $creator) {
            return;
        }

        $creator->notify(new PurchaseOrderStatusNotification($order));

        if ($mailable) {
            Mail::to($creator)->send($mailable);
        }
    }
}
