<?php

namespace App\Models;

use App\Enums\UserIdentityVerificationStatus;
use App\Support\IdentityValueNormalizer;
use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class UserIdentityVerification extends Model implements HasMedia
{
    use HasPublicId;
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'public_id',
        'status',
        'id_type',
        'is_age_confirmed',
        'id_number',
        'date_of_birth',
        'district_id',
        'district_name',
        'county_id',
        'county_name',
        'sub_county_id',
        'sub_county_name',
        'parish_id',
        'parish_name',
        'village_id',
        'village_name',
        'reviewed_by',
        'submitted_at',
        'reviewed_at',
        'verified_at',
        'rejection_reason',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $verification): void {
            $verification->id_number = filled($verification->id_number)
                ? IdentityValueNormalizer::verificationIdNumber($verification->id_number)
                : null;
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => UserIdentityVerificationStatus::class,
            'is_age_confirmed' => 'boolean',
            'date_of_birth' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
    }

    public function hasCompletedAdminIdentityDetails(): bool
    {
        return filled($this->id_number)
            && $this->date_of_birth !== null
            && filled($this->district_id)
            && filled($this->county_id)
            && filled($this->sub_county_id)
            && filled($this->parish_id)
            && filled($this->village_id);
    }

    public function canDeleteIdImages(): bool
    {
        return ($this->status?->value ?? $this->status) === UserIdentityVerificationStatus::Approved->value
            && $this->hasCompletedAdminIdentityDetails();
    }
}
