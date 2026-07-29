<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceOrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string $orderStatus
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectForStatus(),
        );
    }

    public function content(): Content
    {
        $linkedOrder = Order::with('items')
            ->where('order_number', $this->invoice->invoice_no)
            ->first();

        return new Content(
            view: 'emails.invoices.order_status',
            with: [
                'invoice' => $this->invoice->loadMissing('items'),
                'orderStatus' => $this->orderStatus,
                'statusLabel' => $this->labelForStatus(),
                'linkedOrder' => $linkedOrder,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }

    protected function subjectForStatus(): string
    {
        return match ($this->orderStatus) {
            'confirmed' => 'Order Confirmed - ' . $this->invoice->invoice_no,
            'dispatched' => 'Order Dispatched - ' . $this->invoice->invoice_no,
            'delivered' => 'Order Delivered - ' . $this->invoice->invoice_no,
            'cancelled' => 'Order Cancelled - ' . $this->invoice->invoice_no,
            default => 'Order Update - ' . $this->invoice->invoice_no,
        };
    }

    protected function labelForStatus(): string
    {
        return match ($this->orderStatus) {
            'confirmed' => 'Confirmed',
            'dispatched' => 'Dispatched',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->orderStatus)),
        };
    }
}