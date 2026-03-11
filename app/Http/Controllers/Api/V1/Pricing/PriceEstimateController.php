<?php

namespace App\Http\Controllers\Api\V1\Pricing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Pricing\PriceEstimateRequest;
use App\Models\Service;
use App\Models\ServiceTier;
use App\Services\PriceEstimateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PriceEstimateController extends Controller
{
    public function __construct(private readonly PriceEstimateService $priceEstimateService) {}

    public function __invoke(PriceEstimateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $service = Service::query()->where('public_id', $data['service_public_id'])->firstOrFail();
        $serviceTier = ServiceTier::query()
            ->where('public_id', $data['service_tier_public_id'])
            ->where('service_id', $service->id)
            ->where('is_active', true)
            ->first();

        if ($serviceTier === null) {
            throw ValidationException::withMessages([
                'service_tier_public_id' => ['Selected service tier is invalid.'],
            ]);
        }

        $addOnIds = $service->addOns()
            ->whereIn('public_id', $data['add_on_public_ids'] ?? [])
            ->pluck('id')
            ->all();

        $breakdown = $this->priceEstimateService->estimate(
            service: $service,
            serviceTier: $serviceTier,
            addOnIds: $addOnIds,
            locationLat: (float) $data['location_lat'],
            locationLng: (float) $data['location_lng'],
            serviceMinutes: (int) ($data['service_minutes'] ?? $service->duration_minutes),
            promoCode: $data['promo_code'] ?? null,
        );

        return response()->json([
            'service_public_id' => $service->public_id,
            'service_tier_public_id' => $serviceTier->public_id,
            'price_breakdown' => $breakdown,
        ]);
    }
}
