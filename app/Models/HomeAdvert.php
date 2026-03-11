<?php

namespace App\Models;

use App\Traits\HasPublicId;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HomeAdvert extends Model
{
    use HasPublicId;

    public const BUSINESS_TIMEZONE = 'Africa/Kampala';

    protected $fillable = [
        'public_id',
        'title',
        'headline',
        'description',
        'button_text',
        'link_type',
        'link_target',
        'image_url',
        'is_active',
        'sort_order',
        'starts_at',
        'ends_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function scopeVisible(Builder $query): Builder
    {
        // Home advert scheduling is managed in Uganda local time from the
        // admin panel's datetime-local inputs, so visibility checks need to
        // use the same wall-clock timezone.
        $businessNow = CarbonImmutable::now(self::BUSINESS_TIMEZONE)
            ->format('Y-m-d H:i:s');

        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) use ($businessNow): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $businessNow);
            })
            ->where(function (Builder $query) use ($businessNow): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $businessNow);
            });
    }
}
