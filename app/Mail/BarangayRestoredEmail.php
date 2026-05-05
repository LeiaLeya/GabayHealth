<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BarangayRestoredEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $barangayName;
    public string $rhuName;
    public string $restoredAt;
    public string $contactEmail;

    public function __construct(string $barangayName, string $rhuName, string $restoredAt, string $contactEmail)
    {
        $this->barangayName  = $barangayName;
        $this->rhuName       = $rhuName;
        $this->restoredAt    = $restoredAt;
        $this->contactEmail  = $contactEmail;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'GabayHealth – Your Barangay Health Center Account Has Been Restored',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.barangay-restored',
            with: [
                'barangayName' => $this->barangayName,
                'rhuName'      => $this->rhuName,
                'restoredAt'   => $this->restoredAt,
                'contactEmail' => $this->contactEmail,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
