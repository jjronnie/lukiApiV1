<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'item_type',
        'service_id',
        'service_tier_id',
        'add_on_id',
        'name_snapshot',
        'tier_name_snapshot',
        'unit_price_amount',
        'quantity',
        'line_total_amount',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price_amount' => 'integer',
            'quantity' => 'integer',
            'line_total_amount' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function addOn(): BelongsTo
    {
        return $this->belongsTo(ServiceAddOn::class, 'add_on_id');
    }

    public function serviceTier(): BelongsTo
    {
        return $this->belongsTo(ServiceTier::class);
    }
}
