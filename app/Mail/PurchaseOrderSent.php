<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderSent extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public PurchaseOrder $order)
    {
        $this->order->loadMissing(['supplier', 'items.product', 'createdBy', 'approvedBy']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Robert Hotel purchase order '.$this->order->reference_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.purchase-order-sent',
            with: ['order' => $this->order],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => Pdf::loadView('pdf.purchase-order', ['order' => $this->order])->output(),
                $this->order->reference_number.'.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
