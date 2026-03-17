<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\ProviderServiceApprovalStatus;
use App\Enums\ProviderVerificationStatus;
use App\Support\IdentityValueNormalizer;
use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProviderProfile extends Model
{
    use HasPublicId;

    protected $fillable = [
        'user_id',
        'public_id',
        'provider_number',
        'provider_type',
        'display_name',
        'legal_name',
        'bio',
        'phone',
        'address_text',
        'business_name',
        'business_address',
        'onboarding_completed_at',
        'avatar_path',
        'avatar_locked_at',
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
            'provider_number' => 'integer',
            'verified_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'avatar_locked_at' => 'datetime',
            'rating_avg' => 'decimal:2',
            'rating_count' => 'integer',
            'completed_orders_count' => 'integer',
            'cancelled_orders_count' => 'integer',
            'verification_status' => ProviderVerificationStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ProviderProfile $profile): void {
            $profile->display_name = trim((string) $profile->display_name);
            $profile->legal_name = filled($profile->legal_name)
                ? IdentityValueNormalizer::humanName($profile->legal_name)
                : null;
            $profile->phone = filled($profile->phone)
                ? IdentityValueNormalizer::ugandaPhoneLocal($profile->phone)
                : null;
            $profile->business_name = filled($profile->business_name)
                ? trim((string) $profile->business_name)
                : null;
            $profile->business_address = filled($profile->business_address)
                ? trim((string) $profile->business_address)
                : null;
            $profile->address_text = filled($profile->address_text)
                ? trim((string) $profile->address_text)
                : null;
        });

        static::saved(function (ProviderProfile $profile): void {
            if ($profile->provider_number !== null) {
                return;
            }

            $profile->forceFill([
                'provider_number' => static::generateProviderNumber($profile->id),
            ])->saveQuietly();
        });
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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeEligibleForMarketplace(Builder $query): Builder
    {
        return $query
            ->whereNotNull('onboarding_completed_at')
            ->where('verification_status', ProviderVerificationStatus::Approved->value)
            ->whereHas('wallet', function ($walletQuery) {
                $walletQuery->where('status', 'active')
                    ->whereRaw('(balance_amount - hold_amount) >= min_required_amount');
            })
            ->whereHas('availability', function ($availabilityQuery) {
                $availabilityQuery->where('is_online', true)
                    ->where('last_seen_at', '>=', now()->subMinutes(2));
            });
    }

    /**
     * @param  array<int, int>  $serviceIds
     * @param  array<int|string, array<int, int>>  $tierIdsByService
     */
    public function syncServiceEligibility(array $serviceIds, array $tierIdsByService = []): void
    {
        $normalizedServiceIds = collect($serviceIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        $currentProviderServices = $this->providerServices()->get()->keyBy('service_id');
        $keptProviderServiceIds = [];

        foreach ($normalizedServiceIds as $serviceId) {
            /** @var ProviderService $providerService */
            $providerService = $currentProviderServices->get($serviceId)
                ?? $this->providerServices()->create([
                    'service_id' => $serviceId,
                    'is_active' => true,
                    'approval_status' => ProviderServiceApprovalStatus::Approved,
                    'requested_at' => now(),
                ]);

            if (! $providerService->is_active || $providerService->approval_status !== ProviderServiceApprovalStatus::Approved) {
                $providerService->update([
                    'is_active' => true,
                    'approval_status' => ProviderServiceApprovalStatus::Approved,
                    'reviewed_at' => now(),
                    'review_reason' => null,
                ]);
            }

            $keptProviderServiceIds[] = $providerService->id;

            $requestedTierIds = collect($tierIdsByService[$serviceId] ?? $tierIdsByService[(string) $serviceId] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter(fn (int $id) => $id > 0)
                ->unique();

            $eligibleTierIds = ServiceTier::query()
                ->where('service_id', $serviceId)
                ->when(
                    $requestedTierIds->isNotEmpty(),
                    fn ($query) => $query->whereIn('id', $requestedTierIds->all()),
                )
                ->where('is_active', true)
                ->pluck('id')
                ->map(fn ($id) => (int) $id);

            if ($eligibleTierIds->isEmpty()) {
                $eligibleTierIds = ServiceTier::query()
                    ->where('service_id', $serviceId)
                    ->where('is_active', true)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id);
            }

            $providerService->eligibleTiers()->sync(
                $eligibleTierIds
                    ->mapWithKeys(fn (int $id) => [$id => ['is_active' => true]])
                    ->all()
            );
        }

        $this->providerServices()
            ->whereNotIn('id', $keptProviderServiceIds === [] ? [-1] : $keptProviderServiceIds)
            ->update([
                'is_active' => false,
            ]);
    }

    /**
     * @param  array<int, int>  $serviceIds
     */
    public function syncRequestedServices(array $serviceIds): void
    {
        $normalizedServiceIds = collect($serviceIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        $existingServices = $this->providerServices()->get()->keyBy('service_id');
        $keptProviderServiceIds = [];

        foreach ($normalizedServiceIds as $serviceId) {
            /** @var ProviderService $providerService */
            $providerService = $existingServices->get($serviceId)
                ?? $this->providerServices()->create([
                    'service_id' => $serviceId,
                    'is_active' => true,
                    'approval_status' => ProviderServiceApprovalStatus::Pending,
                    'requested_at' => now(),
                ]);

            $keptProviderServiceIds[] = $providerService->id;

            $approvalStatus = $providerService->approval_status?->value ?? $providerService->approval_status;
            if ($approvalStatus !== ProviderServiceApprovalStatus::Approved->value) {
                $providerService->update([
                    'is_active' => true,
                    'approval_status' => ProviderServiceApprovalStatus::Pending,
                    'requested_at' => now(),
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'review_reason' => null,
                ]);
            } elseif (! $providerService->is_active) {
                $providerService->update(['is_active' => true]);
            }
        }

        $this->providerServices()
            ->whereNotIn('id', $keptProviderServiceIds === [] ? [-1] : $keptProviderServiceIds)
            ->update(['is_active' => false]);
    }

    public function earningsThisMonthAmount(): int
    {
        $completedOrders = $this->orders()
            ->where('status', OrderStatus::Completed->value)
            ->whereBetween('completed_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->get(['total_amount', 'transport_fee_amount']);

        $baseAmount = (int) config('luki.commission.base_amount', 1000);
        $percentageRate = (float) config('luki.commission.percentage_rate', 5);
        $excludeTransport = (bool) config('luki.commission.exclude_transport', true);

        return (int) $completedOrders->sum(function (Order $order) use ($baseAmount, $percentageRate, $excludeTransport): int {
            $totalAmount = (int) ($order->total_amount ?? 0);
            $transportAmount = (int) ($order->transport_fee_amount ?? 0);
            $commissionableAmount = $excludeTransport
                ? max(0, $totalAmount - $transportAmount)
                : $totalAmount;
            $commissionAmount = $baseAmount + (int) round($commissionableAmount * ($percentageRate / 100));

            return max(0, $totalAmount - $commissionAmount);
        });
    }

    private static function generateProviderNumber(int $providerProfileId): int
    {
        for ($attempt = 0; $attempt < 100; $attempt++) {
            $candidate = random_int(10000, 99999);
            if (! static::query()
                ->where('provider_number', $candidate)
                ->whereKeyNot($providerProfileId)
                ->exists()) {
                return $candidate;
            }
        }

        throw new RuntimeException('Unable to assign a unique 5-digit provider number.');
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (blank($this->avatar_path)) {
            return null;
        }

        if (Str::startsWith($this->avatar_path, ['http://', 'https://'])) {
            return $this->avatar_path;
        }

        return Storage::disk('public')->url($this->avatar_path);
    }

    public function getIsOnboardingCompleteAttribute(): bool
    {
        return $this->onboarding_completed_at !== null;
    }
}
