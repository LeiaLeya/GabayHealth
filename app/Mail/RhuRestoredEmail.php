<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RhuRestoredEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $rhuName;
    public string $restoredAt;
    public string $contactEmail;

    public function __construct(string $rhuName, string $restoredAt, string $contactEmail)
    {
        $this->rhuName      = $rhuName;
        $this->restoredAt   = $restoredAt;
        $this->contactEmail = $contactEmail;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'GabayHealth – Your RHU Account Has Been Restored',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rhu-restored',
            with: [
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
