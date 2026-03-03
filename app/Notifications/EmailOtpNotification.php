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
            default => 'Your verification code',
        };

        $headline = match ($this->purpose) {
            'email_verification' => 'Confirm your email address',
            'login' => 'Confirm your login',
            'password_reset' => 'Reset your password',
            default => 'Security verification',
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting($headline)
            ->line('Use the 6-digit code below to complete your request.')
            ->line("Code: {$this->code}")
            ->line("This code expires in {$this->expiresInMinutes} minutes.")
            ->line('If you did not request this code, you can ignore this email.');
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
