<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IdentityVerificationDecisionMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $headline,
        public string $messageLine,
        public ?string $reason = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.identity-verification-decision',
            with: [
                'headline' => $this->headline,
                'messageLine' => $this->messageLine,
                'reason' => $this->reason,
            ],
        );
    }

    /**
     * @return array<int, string>
     */
    public function attachments(): array
    {
        return [];
    }
}
