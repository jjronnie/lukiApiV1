<?php

namespace App\Models;

use App\Enums\ProviderVerificationStatus;
use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProviderIdentityVerification extends Model implements HasMedia
{
    use HasPublicId;
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'provider_profile_id',
        'public_id',
        'status',
        'id_type',
        'reviewed_by',
        'submitted_at',
        'reviewed_at',
        'verified_at',
        'rejection_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProviderVerificationStatus::class,
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('selfie')->singleFile();
        $this->addMediaCollection('id_front')->singleFile();
        $this->addMediaCollection('id_back')->singleFile();
        $this->addMediaCollection('business_license')->singleFile();
    }
}
