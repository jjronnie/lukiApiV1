<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportZone extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'center_lat',
        'center_lng',
        'radius_km',
        'fee_amount',
        'is_active',
        'is_fallback',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'center_lat' => 'decimal:7',
            'center_lng' => 'decimal:7',
            'radius_km' => 'decimal:2',
            'fee_amount' => 'integer',
            'is_active' => 'boolean',
            'is_fallback' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
