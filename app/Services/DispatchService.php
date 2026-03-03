<?php

namespace App\Services;

use App\Models\NotificationRecord;
use App\Models\Order;
use App\Models\OrderOffer;
use App\Models\ProviderProfile;
use Illuminate\Support\Collection;

class DispatchService
{
    /**
     * @return Collection<int, ProviderProfile>
     */
    public function eligibleProviders(Order $order): Collection
    {
        $serviceId = $order->items()->where('item_type', 'service')->value('service_id');

        return ProviderProfile::query()
            ->where('verification_status', 'approved')
            ->whereHas('providerServices', function ($query) use ($serviceId) {
                $query->where('service_id', $serviceId)->where('is_active', true);
            })
            ->whereHas('wallet', function ($query) {
                $query->where('status', 'active')
                    ->whereRaw('(balance_amount - hold_amount) >= min_required_amount');
            })
            ->whereHas('availability', function ($query) {
                $query->where('is_online', true)
                    ->where('last_seen_at', '>=', now()->subMinutes(2));
            })
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
