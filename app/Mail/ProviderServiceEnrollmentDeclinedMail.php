<?php

namespace App\Mail;

use App\Models\ProviderProfile;
use App\Models\ProviderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProviderServiceEnrollmentDeclinedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ProviderProfile $providerProfile,
        public ProviderService $providerService,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Luki service enrollment request was declined',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.provider-service-enrollment-declined',
            with: [
                'providerProfile' => $this->providerProfile,
                'providerService' => $this->providerService,
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
