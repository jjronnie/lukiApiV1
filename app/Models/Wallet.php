<?php

namespace App\Models;

use App\Enums\WalletStatus;
use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasPublicId;

    protected $fillable = [
        'provider_profile_id',
        'public_id',
        'currency',
        'balance_amount',
        'hold_amount',
        'min_required_amount',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance_amount' => 'integer',
            'hold_amount' => 'integer',
            'min_required_amount' => 'integer',
            'status' => WalletStatus::class,
        ];
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
