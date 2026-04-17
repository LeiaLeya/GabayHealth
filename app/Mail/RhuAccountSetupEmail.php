<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RhuAccountSetupEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $rhuName;
    public $username;
    public $setupUrl;
    public $expiresAt;

    /**
     * Create a new message instance.
     */
    public function __construct($rhuName, $username, $setupUrl, $expiresAt)
    {
        $this->rhuName = $rhuName;
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
            subject: 'GabayHealth Account Setup - Set Your Password',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.rhu-account-setup',
            with: [
                'rhuName' => $this->rhuName,
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
