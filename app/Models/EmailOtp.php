<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailOtp extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'email',
        'purpose',
        'app_type',
        'otp_hash',
        'token_hash',
        'attempts',
        'resend_count',
        'last_sent_at',
        'resend_window_started_at',
        'expires_at',
        'consumed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'resend_window_started_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
