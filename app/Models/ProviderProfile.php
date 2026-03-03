<?php

namespace App\Models;

use App\Enums\ProviderVerificationStatus;
use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProviderProfile extends Model
{
    use HasPublicId;

    protected $fillable = [
        'user_id',
        'public_id',
        'provider_type',
        'display_name',
        'legal_name',
        'bio',
        'avatar_path',
        'verification_status',
        'verified_at',
        'rejection_reason',
        'rating_avg',
        'rating_count',
        'completed_orders_count',
        'cancelled_orders_count',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'rating_avg' => 'decimal:2',
            'rating_count' => 'integer',
            'completed_orders_count' => 'integer',
            'cancelled_orders_count' => 'integer',
            'verification_status' => ProviderVerificationStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'provider_services')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function providerServices(): HasMany
    {
        return $this->hasMany(ProviderService::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProviderDocument::class);
    }

    public function availability(): HasOne
    {
        return $this->hasOne(ProviderAvailability::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(ProviderLocation::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function offeredOrders(): HasMany
    {
        return $this->hasMany(OrderOffer::class);
    }
}
