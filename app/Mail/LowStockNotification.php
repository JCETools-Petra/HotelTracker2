<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LowStockNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The collection of low stock items.
     *
     * @var \Illuminate\Support\Collection
     */
    public $lowStockItems;

    /**
     * Create a new message instance.
     */
    public function __construct(Collection $lowStockItems)
    {
        $this->lowStockItems = $lowStockItems;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Notifikasi Stok Rendah',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Baris inilah yang memberitahu Laravel untuk merender file sebagai email Markdown
        return new Content(
            markdown: 'emails.low_stock_notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}