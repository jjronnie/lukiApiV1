<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEmailPreference extends Model
{
    protected $fillable = [
        'user_id',
        'marketing_emails_enabled',
        'booking_emails_enabled',
        'authentication_emails_enabled',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'marketing_emails_enabled' => 'boolean',
            'booking_emails_enabled' => 'boolean',
            'authentication_emails_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
