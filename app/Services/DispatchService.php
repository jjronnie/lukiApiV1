<?php

namespace App\Services;

use App\Enums\MobileAppType;
use App\Enums\OrderStatus;
use App\Enums\ProviderServiceApprovalStatus;
use App\Enums\ProviderVerificationStatus;
use App\Jobs\DispatchOrderOffersJob;
use App\Models\Order;
use App\Models\OrderOffer;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\ServiceTier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DispatchService
{
    public function __construct(
        private readonly NotificationDispatcher $notificationDispatcher,
    ) {}

    /**
     * @param  array<int, int>  $excludeProviderIds
     * @return Collection<int, ProviderProfile>
     */
    public function eligibleProviders(Order $order, array $excludeProviderIds = []): Collection
    {
        if ($order->service_id === null || $order->service_tier_id === null) {
            return collect();
        }

        return ProviderProfile::query()
            ->whereNotNull('onboarding_completed_at')
            ->whereHas('user', fn ($query) => $query->where('is_blocked', false))
            ->whereDoesntHave('orders', function ($query) {
                $query->whereIn('status', Order::providerBusyStatusValues());
            })
            ->whereHas('providerServices', function ($query) use ($order) {
                $query->where('service_id', $order->service_id)
                    ->where('is_active', true)
                    ->where('approval_status', ProviderServiceApprovalStatus::Approved->value)
                    ->whereHas('eligibleTiers', function ($tierQuery) use ($order) {
                        $tierQuery
                            ->where('service_tiers.id', $order->service_tier_id)
                            ->where('provider_service_tiers.is_active', true);
                    });
            })
            ->when(
                $excludeProviderIds !== [],
                fn ($query) => $query->whereNotIn('id', $excludeProviderIds),
            )
            ->eligibleForMarketplace()
            ->with([
                'availability',
                'user',
                'wallet',
                'providerServices' => fn ($query) => $query
                    ->where('service_id', $order->service_id)
                    ->with(['eligibleTiers' => fn ($tierQuery) => $tierQuery
                        ->where('service_tiers.id', $order->service_tier_id)
                        ->where('provider_service_tiers.is_active', true),
                    ]),
                'locations' => fn ($query) => $query->latest('recorded_at')->limit(1),
            ])
            ->get()
            ->filter(fn (ProviderProfile $provider) => $this->qualifiesForOrder($provider, $order))
            ->sortByDesc(fn (ProviderProfile $provider) => $this->scoreProvider($provider, $order))
            ->values();
    }

    public function startOrderDispatch(Order $order): int
    {
        return $this->dispatchNextBatch($order, 1);
    }

    public function dispatchNextBatch(Order $order, ?int $batchNo = null): int
    {
        $order = $order->fresh([
            'offers',
            'service.tiers' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('price_amount'),
        ]) ?? $order;

        if ($order->status !== OrderStatus::Offering || $order->provider_profile_id !== null) {
            return 0;
        }

        $this->expireStaleOffers($order);

        if ($order->offers()->where('status', 'accepted')->exists()) {
            return 0;
        }

        if ($order->offers()->where('status', 'pending')->where('expires_at', '>', now())->exists()) {
            return 0;
        }

        $alreadyContactedProviderIds = $order->offers()
            ->pluck('provider_profile_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $eligible = $this->eligibleProviders($order, $alreadyContactedProviderIds);
        if ($eligible->isEmpty()) {
            $this->markSearchExhausted($order);

            return 0;
        }

        $batchSize = (int) config('luki.dispatch.offer_batch_size', 5);
        $expirySeconds = (int) config('luki.dispatch.offer_expiry_seconds', 30);
        $currentBatch = $batchNo ?? (((int) $order->offers()->max('batch_no')) + 1);
        $offersCreated = 0;

        foreach ($eligible->take($batchSize) as $provider) {
            OrderOffer::query()->create([
                'order_id' => $order->id,
                'provider_profile_id' => $provider->id,
                'batch_no' => $currentBatch,
                'status' => 'pending',
                'expires_at' => now()->addSeconds($expirySeconds),
                'meta' => [
                    'score' => $this->scoreProvider($provider, $order),
                    'distance_km' => $this->distanceFromOrderKm($provider, $order),
                ],
                'created_at' => now(),
            ]);

            if ($provider->user !== null) {
                $this->notificationDispatcher->sendToUser(
                    $provider->user,
                    MobileAppType::Provider,
                    'provider_request',
                    'New order offer',
                    'A nearby customer needs your service.',
                    [
                        'screen' => 'provider_orders',
                        'order_id' => $order->public_id,
                    ],
                    $provider,
                );
            }

            $offersCreated++;
        }

        DispatchOrderOffersJob::dispatch($order->id, $currentBatch + 1)
            ->delay(now()->addSeconds($expirySeconds));

        return $offersCreated;
    }

    public function refillPendingOffers(Order $order): int
    {
        $order = $order->fresh(['offers']) ?? $order;

        if ($order->status !== OrderStatus::Offering || $order->provider_profile_id !== null) {
            return 0;
        }

        $this->expireStaleOffers($order);

        $batchSize = (int) config('luki.dispatch.offer_batch_size', 5);
        $expirySeconds = (int) config('luki.dispatch.offer_expiry_seconds', 30);
        $activePendingCount = (int) $order->offers()
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->count();

        $slotsToFill = max(0, $batchSize - $activePendingCount);
        if ($slotsToFill === 0) {
            return 0;
        }

        $alreadyContactedProviderIds = $order->offers()
            ->pluck('provider_profile_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $eligible = $this->eligibleProviders($order, $alreadyContactedProviderIds)->take($slotsToFill);
        if ($eligible->isEmpty()) {
            if ($activePendingCount === 0) {
                $this->markSearchExhausted($order);
            }

            return 0;
        }

        $batchNo = ((int) $order->offers()->max('batch_no')) + 1;
        $offersCreated = 0;

        foreach ($eligible as $provider) {
            OrderOffer::query()->create([
                'order_id' => $order->id,
                'provider_profile_id' => $provider->id,
                'batch_no' => $batchNo,
                'status' => 'pending',
                'expires_at' => now()->addSeconds($expirySeconds),
                'meta' => [
                    'score' => $this->scoreProvider($provider, $order),
                    'distance_km' => $this->distanceFromOrderKm($provider, $order),
                    'dispatch_reason' => 'skip_backfill',
                ],
                'created_at' => now(),
            ]);

            if ($provider->user !== null) {
                $this->notificationDispatcher->sendToUser(
                    $provider->user,
                    MobileAppType::Provider,
                    'provider_request',
                    'New order offer',
                    'A nearby customer needs your service.',
                    [
                        'screen' => 'provider_orders',
                        'order_id' => $order->public_id,
                    ],
                    $provider,
                );
            }

            $offersCreated++;
        }

        return $offersCreated;
    }

    public function offerPair(Order $order, ProviderProfile $provider, int $expirySeconds = 30): void
    {
        OrderOffer::query()->create([
            'order_id' => $order->id,
            'provider_profile_id' => $provider->id,
            'batch_no' => 1,
            'status' => 'pending',
            'expires_at' => now()->addSeconds($expirySeconds),
            'meta' => [
                'dispatch_mode' => 'pair',
                'provider_number' => $provider->provider_number,
            ],
            'created_at' => now(),
        ]);

        if ($provider->user !== null) {
            $this->notificationDispatcher->sendToUser(
                $provider->user,
                MobileAppType::Provider,
                'provider_pair_request',
                'Direct pair request',
                'A customer requested you directly by your provider number.',
                [
                    'screen' => 'provider_orders',
                    'order_id' => $order->public_id,
                ],
                $provider,
            );
        }
    }

    /**
     * @return array{provider: ProviderProfile|null, message: string|null}
     */
    public function resolvePairProvider(Service $service, ServiceTier $serviceTier, int $providerNumber): array
    {
        $provider = ProviderProfile::query()
            ->where('provider_number', $providerNumber)
            ->with([
                'availability',
                'locations' => fn ($query) => $query->latest('recorded_at')->limit(1),
                'user',
                'wallet',
                'providerServices' => fn ($query) => $query
                    ->where('service_id', $service->id)
                    ->where('is_active', true)
                    ->where('approval_status', ProviderServiceApprovalStatus::Approved->value)
                    ->with(['eligibleTiers' => fn ($tierQuery) => $tierQuery
                        ->where('service_tiers.id', $serviceTier->id)
                        ->where('provider_service_tiers.is_active', true),
                    ]),
            ])
            ->first();

        if ($provider === null) {
            return [
                'provider' => null,
                'message' => 'No provider was found with that number.',
            ];
        }

        if (! $this->qualifiesForServiceTier($provider, $service, $serviceTier)) {
            return [
                'provider' => null,
                'message' => 'This provider is not eligible for the selected service right now.',
            ];
        }

        return [
            'provider' => $provider,
            'message' => null,
        ];
    }

    public function syncSearchState(Order $order): Order
    {
        $order = $order->fresh([
            'items',
            'providerProfile.user',
            'providerProfile.availability',
            'service.category',
            'serviceTier',
            'statusHistories',
            'offers',
        ]) ?? $order;

        if ($order->status !== OrderStatus::Offering || $order->provider_profile_id !== null) {
            return $order;
        }

        $this->expireStaleOffers($order);

        if ($order->offers()->where('status', 'accepted')->exists()) {
            return $order->fresh([
                'items',
                'providerProfile.user',
                'providerProfile.availability',
                'service.category',
                'serviceTier',
                'statusHistories',
                'offers',
            ]) ?? $order;
        }

        if ($order->offers()->where('status', 'pending')->where('expires_at', '>', now())->exists()) {
            return $order;
        }

        $alreadyContactedProviderIds = $order->offers()
            ->pluck('provider_profile_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($this->eligibleProviders($order, $alreadyContactedProviderIds)->isEmpty()) {
            $this->markSearchExhausted($order);
        }

        return $order->fresh([
            'items',
            'providerProfile.user',
            'providerProfile.availability',
            'service.category',
            'serviceTier',
            'statusHistories',
            'offers',
        ]) ?? $order;
    }

    public function expireStaleOffers(Order $order): int
    {
        return OrderOffer::query()
            ->where('order_id', $order->id)
            ->where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->update([
                'status' => 'expired',
                'responded_at' => now(),
            ]);
    }

    private function markSearchExhausted(Order $order): void
    {
        $didExpire = false;

        DB::transaction(function () use ($order, &$didExpire): void {
            $freshOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($freshOrder->status !== OrderStatus::Offering || $freshOrder->provider_profile_id !== null) {
                return;
            }

            $freshOrder->update([
                'status' => OrderStatus::Expired,
                'expired_at' => now(),
                'cancellation_reason' => 'failed_to_find_provider',
            ]);

            $freshOrder->statusHistories()->create([
                'from_status' => OrderStatus::Offering->value,
                'to_status' => OrderStatus::Expired->value,
                'changed_by_user_id' => $freshOrder->user_id,
                'meta' => ['source' => 'dispatch_timeout_or_exhausted'],
                'created_at' => now(),
            ]);

            $didExpire = true;
        });

        if (! $didExpire) {
            return;
        }
    }

    private function qualifiesForOrder(ProviderProfile $provider, Order $order): bool
    {
        if (($provider->verification_status?->value ?? $provider->verification_status) !== ProviderVerificationStatus::Approved->value) {
            return false;
        }

        if ($provider->onboarding_completed_at === null) {
            return false;
        }

        if (! $this->isWithinSchedule($provider)) {
            return false;
        }

        if (! $this->hasFreshLocation($provider)) {
            return false;
        }

        return $this->isTierAllowedForProviderRating($provider, $order);
    }

    private function qualifiesForServiceTier(
        ProviderProfile $provider,
        Service $service,
        ServiceTier $serviceTier,
    ): bool {
        if (($provider->verification_status?->value ?? $provider->verification_status) !== ProviderVerificationStatus::Approved->value) {
            return false;
        }

        if ($provider->onboarding_completed_at === null) {
            return false;
        }

        if ($provider->wallet === null
            || (($provider->wallet->status?->value ?? $provider->wallet->status) !== 'active')
            || ($provider->wallet->balance_amount - $provider->wallet->hold_amount) < $provider->wallet->min_required_amount) {
            return false;
        }

        if ($provider->availability === null
            || ! $provider->availability->is_online
            || $provider->availability->last_seen_at === null
            || $provider->availability->last_seen_at->lt(now()->subSeconds((int) config('luki.dispatch.location_freshness_seconds', 180)))) {
            return false;
        }

        if ($provider->orders()->whereIn('status', Order::providerBusyStatusValues())->exists()) {
            return false;
        }

        if (! $this->hasFreshLocation($provider) || ! $this->isWithinSchedule($provider)) {
            return false;
        }

        $providerService = $provider->providerServices->first();
        if ($providerService === null || ! $providerService->isApproved() || $providerService->eligibleTiers->isEmpty()) {
            return false;
        }

        $tierRank = $this->serviceTierRank($service, $serviceTier);
        $maxAllowedTierRank = $this->maxAllowedTierRank($provider, $service);

        return $tierRank <= $maxAllowedTierRank;
    }

    private function isTierAllowedForProviderRating(ProviderProfile $provider, Order $order): bool
    {
        $service = $order->relationLoaded('service') ? $order->service : null;
        $service ??= Service::query()->with(['tiers' => fn ($query) => $query
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price_amount'),
        ])->find($order->service_id);

        $serviceTier = $order->relationLoaded('serviceTier') ? $order->serviceTier : null;
        $serviceTier ??= ServiceTier::query()->find($order->service_tier_id);

        if ($service === null || $serviceTier === null) {
            return false;
        }

        $tierRank = $this->serviceTierRank($service, $serviceTier);
        $maxAllowedTierRank = $this->maxAllowedTierRank($provider, $service);

        return $tierRank <= $maxAllowedTierRank;
    }

    private function maxAllowedTierRank(ProviderProfile $provider, Service $service): int
    {
        $service->loadMissing(['tiers' => fn ($query) => $query
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price_amount'),
        ]);

        $totalTiers = max(1, $service->tiers->count());
        $rating = (float) $provider->rating_avg;

        if ($rating <= 2.0) {
            return 1;
        }

        if ($rating <= 3.0) {
            return min(2, $totalTiers);
        }

        return $totalTiers;
    }

    private function serviceTierRank(Service $service, ServiceTier $serviceTier): int
    {
        $service->loadMissing(['tiers' => fn ($query) => $query
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price_amount'),
        ]);

        $orderedTierIds = $service->tiers->pluck('id')->values();
        $index = $orderedTierIds->search($serviceTier->id);

        return $index === false ? $orderedTierIds->count() : ((int) $index + 1);
    }

    private function hasFreshLocation(ProviderProfile $provider): bool
    {
        $latestLocation = $provider->locations->first();

        return $latestLocation !== null
            && $latestLocation->recorded_at !== null
            && $latestLocation->recorded_at->gte(
                now()->subSeconds((int) config('luki.dispatch.location_freshness_seconds', 180))
            );
    }

    private function distanceFromOrderKm(ProviderProfile $provider, Order $order): ?float
    {
        $latestLocation = $provider->locations->first();
        if ($latestLocation === null) {
            return null;
        }

        return $this->distanceKm(
            (float) $latestLocation->lat,
            (float) $latestLocation->lng,
            (float) $order->location_lat,
            (float) $order->location_lng,
        );
    }

    private function scoreProvider(ProviderProfile $provider, Order $order): float
    {
        $distancePenalty = (($this->distanceFromOrderKm($provider, $order) ?? 999) * 2.5);
        $ratingScore = (float) $provider->rating_avg * 18;
        $completedScore = min(18, $provider->completed_orders_count * 0.4);
        $cancellationPenalty = min(16, $provider->cancelled_orders_count * 1.4);
        $fairnessBoost = max(0, 14 - ($provider->completed_orders_count * 0.2));

        return $ratingScore + $completedScore + $fairnessBoost - $cancellationPenalty - $distancePenalty;
    }

    private function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function isWithinSchedule(ProviderProfile $provider): bool
    {
        $availability = $provider->availability;
        if ($availability === null || empty($availability->weekly_schedule)) {
            return true;
        }

        $schedule = (array) $availability->weekly_schedule;
        $now = now()->setTimezone($availability->timezone ?? 'Africa/Kampala');
        $dayKey = strtolower($now->format('D'));
        $daySchedule = (array) ($schedule[$dayKey] ?? []);

        if ($daySchedule === []) {
            return true;
        }

        $currentMinutes = ((int) $now->format('H') * 60) + (int) $now->format('i');
        $startMinutes = (int) ($daySchedule['start_minutes'] ?? 0);
        $endMinutes = (int) ($daySchedule['end_minutes'] ?? 1440);

        return $currentMinutes >= $startMinutes && $currentMinutes <= $endMinutes;
    }
}
