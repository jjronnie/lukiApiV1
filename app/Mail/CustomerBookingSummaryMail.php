<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class CustomerBookingSummaryMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Order $order,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Luki booking summary',
        );
    }

    public function content(): Content
    {
        $preferenceUrl = URL::temporarySignedRoute(
            'email-preferences.show',
            now()->addDays((int) config('luki.email_preferences.signed_url_days', 30)),
            ['user' => $this->order->user_id],
        );

        return new Content(
            view: 'emails.customer-booking-summary',
            with: [
                'order' => $this->order,
                'preferenceUrl' => $preferenceUrl,
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
