<?php

namespace App\Models;

use App\Enums\VerificationSessionFlow;
use App\Enums\VerificationSessionStatus;
use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

class VerificationSession extends Model
{
    use HasPublicId;

    protected $fillable = [
        'user_id',
        'public_id',
        'session_key',
        'flow',
        'status',
        'expires_at',
        'started_at',
        'submitted_at',
        'completed_at',
        'cancelled_at',
        'ip_address',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => VerificationSessionStatus::class,
            'flow' => VerificationSessionFlow::class,
            'expires_at' => 'datetime',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === VerificationSessionStatus::Open && ! $this->hasExpired();
    }

    public function shouldExpire(): bool
    {
        return $this->status === VerificationSessionStatus::Open && $this->hasExpired();
    }

    public function markExpired(): void
    {
        if (! $this->shouldExpire()) {
            return;
        }

        $this->forceFill([
            'status' => VerificationSessionStatus::Expired,
        ])->save();
    }

    public function signedUrl(): string
    {
        return URL::temporarySignedRoute(
            'verification.sessions.show',
            $this->expires_at,
            [
                'session' => $this,
                'session_key' => $this->session_key,
            ],
        );
    }
}
