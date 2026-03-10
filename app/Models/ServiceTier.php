<?php

namespace App\Models;

use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServiceTier extends Model
{
    use HasPublicId;

    protected $fillable = [
        'service_id',
        'public_id',
        'name',
        'slug',
        'price_amount',
        'description',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_amount' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (ServiceTier $tier): void {
            $tier->service?->refreshBasePriceFromTiers();
        });

        static::deleted(function (ServiceTier $tier): void {
            $tier->service?->refreshBasePriceFromTiers();
        });
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function providerServices(): BelongsToMany
    {
        return $this->belongsToMany(ProviderService::class, 'provider_service_tiers')
            ->withPivot('is_active')
            ->withTimestamps();
    }
}
