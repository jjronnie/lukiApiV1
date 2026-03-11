<?php

namespace App\Models;

use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'service_category_id',
        'slug',
        'name',
        'icon_name',
        'image_url',
        'description',
        'currency',
        'base_price_amount',
        'duration_minutes',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_price_amount' => 'integer',
            'duration_minutes' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function addOns(): HasMany
    {
        return $this->hasMany(ServiceAddOn::class);
    }

    public function pricingRules(): HasMany
    {
        return $this->hasMany(ServicePricingRule::class);
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(ProviderProfile::class, 'provider_services')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(ServiceTier::class)->orderBy('sort_order')->orderBy('price_amount');
    }

    public function refreshBasePriceFromTiers(): void
    {
        $startingPrice = $this->tiers()->where('is_active', true)->min('price_amount');

        $this->forceFill([
            'base_price_amount' => $startingPrice ?? 0,
        ])->saveQuietly();
    }
}
