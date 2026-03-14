<?php

namespace App\Models;

use App\Enums\ProviderServiceApprovalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProviderService extends Model
{
    protected $fillable = [
        'provider_profile_id',
        'service_id',
        'is_active',
        'approval_status',
        'requested_at',
        'reviewed_by',
        'reviewed_at',
        'review_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'approval_status' => ProviderServiceApprovalStatus::class,
            'requested_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function tierQualifications(): HasMany
    {
        return $this->hasMany(ProviderServiceTier::class);
    }

    public function eligibleTiers(): BelongsToMany
    {
        return $this->belongsToMany(ServiceTier::class, 'provider_service_tiers')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function isApproved(): bool
    {
        return $this->is_active
            && ($this->approval_status?->value ?? $this->approval_status) === ProviderServiceApprovalStatus::Approved->value;
    }
}
