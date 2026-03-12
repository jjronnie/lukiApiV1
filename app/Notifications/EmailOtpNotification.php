<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailOtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $code,
        public string $purpose,
        public int $expiresInMinutes,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->purpose) {
            'email_verification' => 'Verify your email address',
            'login' => 'Your login verification code',
            'password_reset' => 'Your password reset code',
            'password_change' => 'Your password change verification code',
            default => 'Your verification code',
        };

        $headline = match ($this->purpose) {
            'email_verification' => 'Your Email Verification Code',
            'login' => 'Your Login Verification Code',
            'password_reset' => 'Your Password Reset Code',
            'password_change' => 'Your Password Change Verification Code',
            default => 'Your Verification Code',
        };

        $intro = match ($this->purpose) {
            'email_verification' => 'Use the code below to confirm your email address.',
            'login' => 'Use the code below to finish signing in.',
            'password_reset' => 'Use the code below to continue resetting your password.',
            'password_change' => 'Use the code below to confirm your password change request.',
            default => 'Use the code below to complete your request.',
        };

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.otp-code', [
                'headline' => $headline,
                'intro' => $intro,
                'code' => $this->code,
                'expiresInMinutes' => $this->expiresInMinutes,
                'expiresAt' => now()->addMinutes($this->expiresInMinutes),
                'appName' => config('app.name', 'Luki Online'),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'purpose' => $this->purpose,
        ];
    }
}
