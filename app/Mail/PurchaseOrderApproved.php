<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderApproved extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public PurchaseOrder $order)
    {
        $this->order->loadMissing(['supplier', 'items.product', 'createdBy', 'approvedBy']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Purchase order '.$this->order->reference_number.' was approved',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.purchase-order-approved',
            with: ['order' => $this->order],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
