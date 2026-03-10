<?php

namespace App\Services;

use App\Models\NotificationRecord;
use App\Models\Order;
use App\Models\OrderOffer;
use App\Models\ProviderProfile;
use App\Models\Service;
use Illuminate\Support\Collection;

class DispatchService
{
    /**
     * @return Collection<int, ProviderProfile>
     */
    public function eligibleProviders(Order $order): Collection
    {
        $serviceId = $order->service_id;
        $serviceTierId = $order->service_tier_id;

        if ($serviceId === null || $serviceTierId === null) {
            return collect();
        }

        return ProviderProfile::query()
            ->whereHas('providerServices', function ($query) use ($serviceId) {
                $query->where('service_id', $serviceId)
                    ->where('is_active', true);
            })
            ->whereHas('providerServices', function ($query) use ($serviceId, $serviceTierId) {
                $query->where('service_id', $serviceId)
                    ->where('is_active', true)
                    ->whereHas('eligibleTiers', function ($tierQuery) use ($serviceTierId) {
                        $tierQuery
                            ->where('service_tiers.id', $serviceTierId)
                            ->where('provider_service_tiers.is_active', true);
                    });
            })
            ->eligibleForMarketplace()
            ->with(['availability', 'locations' => fn ($query) => $query->latest('recorded_at')->limit(1)])
            ->get()
            ->filter(fn (ProviderProfile $provider) => $this->isWithinSchedule($provider))
            ->sortByDesc(fn (ProviderProfile $provider) => $this->scoreProvider($provider, $order))
            ->values();
    }

    public function offerInBatches(Order $order, int $batchSize = 3, int $expirySeconds = 15): void
    {
        $eligible = $this->eligibleProviders($order);
        if ($eligible->isEmpty()) {
            return;
        }

        $batchNo = 1;
        foreach ($eligible->chunk($batchSize) as $chunk) {
            foreach ($chunk as $provider) {
                OrderOffer::query()->create([
                    'order_id' => $order->id,
                    'provider_profile_id' => $provider->id,
                    'batch_no' => $batchNo,
                    'status' => 'pending',
                    'expires_at' => now()->addSeconds($expirySeconds),
                    'meta' => [
                        'score' => $this->scoreProvider($provider, $order),
                    ],
                    'created_at' => now(),
                ]);

                NotificationRecord::query()->create([
                    'provider_profile_id' => $provider->id,
                    'user_id' => $provider->user_id,
                    'type' => 'dispatch.offer',
                    'title' => 'New order offer',
                    'body' => 'A nearby customer needs your service.',
                    'payload' => ['order_public_id' => $order->public_id],
                ]);
            }
            $batchNo++;
        }
    }

    /**
     * @return array{provider: ProviderProfile|null, message: string|null}
     */
    public function resolvePairProvider(Service $service, int $providerNumber): array
    {
        $provider = ProviderProfile::query()
            ->where('provider_number', $providerNumber)
            ->with([
                'availability',
                'wallet',
                'providerServices' => fn ($query) => $query
                    ->where('service_id', $service->id)
                    ->where('is_active', true),
            ])
            ->first();

        if ($provider === null) {
            return [
                'provider' => null,
                'message' => 'No provider was found with that number.',
            ];
        }

        if ($provider->verification_status?->value !== 'approved'
            && $provider->verification_status !== 'approved') {
            return [
                'provider' => null,
                'message' => 'This provider is not approved to receive bookings right now.',
            ];
        }

        if ($provider->providerServices->isEmpty()) {
            return [
                'provider' => null,
                'message' => 'This provider does not offer the selected service.',
            ];
        }

        if ($provider->wallet === null
            || (($provider->wallet->status?->value ?? $provider->wallet->status) !== 'active')
            || ($provider->wallet->balance_amount - $provider->wallet->hold_amount) < $provider->wallet->min_required_amount) {
            return [
                'provider' => null,
                'message' => 'This provider is currently unavailable.',
            ];
        }

        if ($provider->availability === null
            || ! $provider->availability->is_online
            || $provider->availability->last_seen_at === null
            || $provider->availability->last_seen_at->lt(now()->subMinutes(2))) {
            return [
                'provider' => null,
                'message' => 'This provider is unavailable right now.',
            ];
        }

        if (! $this->isWithinSchedule($provider)) {
            return [
                'provider' => null,
                'message' => 'This provider is outside their working hours right now.',
            ];
        }

        return [
            'provider' => $provider,
            'message' => null,
        ];
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

        NotificationRecord::query()->create([
            'provider_profile_id' => $provider->id,
            'user_id' => $provider->user_id,
            'type' => 'dispatch.offer.pair',
            'title' => 'Direct pair request',
            'body' => 'A customer requested you directly by your provider number.',
            'payload' => ['order_public_id' => $order->public_id],
        ]);
    }

    private function scoreProvider(ProviderProfile $provider, ?Order $order = null): float
    {
        $distancePenalty = 0.0;
        if ($provider->locations->isNotEmpty()) {
            $latestLocation = $provider->locations->first();
            if ($latestLocation !== null && $order !== null) {
                $distanceKm = $this->distanceKm(
                    (float) $latestLocation->lat,
                    (float) $latestLocation->lng,
                    (float) $order->location_lat,
                    (float) $order->location_lng,
                );
                $distancePenalty = $distanceKm * 1.5;
            }
        }

        $ratingScore = (float) $provider->rating_avg * 20;
        $completedScore = min(20, $provider->completed_orders_count / 5);
        $cancellationPenalty = min(20, $provider->cancelled_orders_count / 2);
        $fairnessBoost = max(0, 15 - ($provider->completed_orders_count / 10));

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
