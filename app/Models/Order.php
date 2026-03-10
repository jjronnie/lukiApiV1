<?php

namespace App\Models;

use App\Enums\OrderBookingMode;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'user_id',
        'provider_profile_id',
        'service_id',
        'service_tier_id',
        'service_name_snapshot',
        'service_tier_name_snapshot',
        'status',
        'booking_mode',
        'pair_provider_number',
        'is_scheduled',
        'scheduled_at',
        'offering_started_at',
        'accepted_at',
        'on_the_way_at',
        'arrived_at',
        'in_service_at',
        'completed_at',
        'cancelled_at',
        'expired_at',
        'cancelled_by_user_id',
        'cancellation_reason',
        'cancellation_fee_amount',
        'address_text',
        'location_lat',
        'location_lng',
        'place_id',
        'location_notes',
        'payment_method',
        'payment_status',
        'paid_at',
        'subtotal_amount',
        'distance_fee_amount',
        'overtime_fee_amount',
        'peak_fee_amount',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'price_breakdown',
        'promo_code',
        'provider_rating',
        'provider_review',
        'rated_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_scheduled' => 'boolean',
            'scheduled_at' => 'datetime',
            'offering_started_at' => 'datetime',
            'accepted_at' => 'datetime',
            'on_the_way_at' => 'datetime',
            'arrived_at' => 'datetime',
            'in_service_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'expired_at' => 'datetime',
            'paid_at' => 'datetime',
            'location_lat' => 'decimal:7',
            'location_lng' => 'decimal:7',
            'subtotal_amount' => 'integer',
            'distance_fee_amount' => 'integer',
            'overtime_fee_amount' => 'integer',
            'peak_fee_amount' => 'integer',
            'tax_amount' => 'integer',
            'discount_amount' => 'integer',
            'total_amount' => 'integer',
            'cancellation_fee_amount' => 'integer',
            'provider_rating' => 'integer',
            'rated_at' => 'datetime',
            'price_breakdown' => 'array',
            'status' => OrderStatus::class,
            'booking_mode' => OrderBookingMode::class,
            'pair_provider_number' => 'integer',
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
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

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceTier(): BelongsTo
    {
        return $this->belongsTo(ServiceTier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(OrderOffer::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }
}
