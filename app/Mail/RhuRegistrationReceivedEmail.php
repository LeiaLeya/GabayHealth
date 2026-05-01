<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RhuRegistrationReceivedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $rhuName;

    public function __construct(string $rhuName)
    {
        $this->rhuName = $rhuName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'GabayHealth – Registration Received',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rhu-registration-received',
            with: ['rhuName' => $this->rhuName],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
