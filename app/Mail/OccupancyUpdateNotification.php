<?php

namespace App\Mail;

use App\Models\DailyOccupancy;
use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OccupancyUpdateNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $property;
    public $occupancy;

    /**
     * Create a new message instance.
     */
    public function __construct(Property $property, DailyOccupancy $occupancy)
    {
        $this->property = $property;
        $this->occupancy = $occupancy;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Ambil dan format tanggal serta waktu
        $dateString = \Carbon\Carbon::parse($this->occupancy->date)->translatedFormat('d F Y');
        $timeString = now()->format('H:i'); // Format jam:menit (contoh: 15:30)

        // Buat subjek email yang baru dan lebih unik
        $subject = "Update Okupansi {$this->property->name} - {$dateString} Pukul {$timeString}";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.occupancy_update',
        );
    }
}