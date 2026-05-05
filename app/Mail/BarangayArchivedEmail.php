<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BarangayArchivedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $barangayName;
    public string $rhuName;
    public string $archivedAt;
    public string $contactEmail;

    public function __construct(string $barangayName, string $rhuName, string $archivedAt, string $contactEmail)
    {
        $this->barangayName = $barangayName;
        $this->rhuName      = $rhuName;
        $this->archivedAt   = $archivedAt;
        $this->contactEmail = $contactEmail;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'GabayHealth – Your Barangay Health Center Account Has Been Archived',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.barangay-archived',
            with: [
                'barangayName' => $this->barangayName,
                'rhuName'      => $this->rhuName,
                'archivedAt'   => $this->archivedAt,
                'contactEmail' => $this->contactEmail,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
