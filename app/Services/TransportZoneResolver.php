<?php

namespace App\Services;

use App\Models\TransportZone;

class TransportZoneResolver
{
    /**
     * @return array{zone: ?TransportZone, fee_amount: int, zone_name: ?string}
     */
    public function resolve(float $lat, float $lng): array
    {
        $matchingZone = TransportZone::query()
            ->where('is_active', true)
            ->where('is_fallback', false)
            ->orderBy('radius_km')
            ->orderBy('sort_order')
            ->get()
            ->first(function (TransportZone $zone) use ($lat, $lng): bool {
                if ($zone->center_lat === null || $zone->center_lng === null || $zone->radius_km === null) {
                    return false;
                }

                return $this->haversineKm(
                    $lat,
                    $lng,
                    (float) $zone->center_lat,
                    (float) $zone->center_lng,
                ) <= (float) $zone->radius_km;
            });

        $zone = $matchingZone
            ?? TransportZone::query()
                ->where('is_active', true)
                ->where('is_fallback', true)
                ->orderBy('sort_order')
                ->first();

        return [
            'zone' => $zone,
            'fee_amount' => (int) ($zone?->fee_amount ?? 0),
            'zone_name' => $zone?->name,
        ];
    }

    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371.0;

        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($deltaLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }
}
