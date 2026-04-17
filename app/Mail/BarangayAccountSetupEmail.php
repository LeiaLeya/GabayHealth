<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BarangayAccountSetupEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $barangayName;
    public $username;
    public $setupUrl;
    public $expiresAt;

    /**
     * Create a new message instance.
     */
    public function __construct($barangayName, $username, $setupUrl, $expiresAt)
    {
        $this->barangayName = $barangayName;
        $this->username = $username;
        $this->setupUrl = $setupUrl;
        $this->expiresAt = $expiresAt;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'GabayHealth Barangay Health Center Account Setup - Set Your Password',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.barangay-account-setup',
            with: [
                'barangayName' => $this->barangayName,
                'username' => $this->username,
                'setupUrl' => $this->setupUrl,
                'expiresAt' => $this->expiresAt,
            ],
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
