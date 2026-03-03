<?php

namespace App\Http\Controllers\Api\V1\Pricing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Pricing\PriceEstimateRequest;
use App\Models\Service;
use App\Services\PriceEstimateService;
use Illuminate\Http\JsonResponse;

class PriceEstimateController extends Controller
{
    public function __construct(private readonly PriceEstimateService $priceEstimateService) {}

    public function __invoke(PriceEstimateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $service = Service::query()->where('public_id', $data['service_public_id'])->firstOrFail();
        $addOnIds = $service->addOns()
            ->whereIn('public_id', $data['add_on_public_ids'] ?? [])
            ->pluck('id')
            ->all();

        $breakdown = $this->priceEstimateService->estimate(
            service: $service,
            addOnIds: $addOnIds,
            distanceKm: (float) ($data['distance_km'] ?? 0),
            serviceMinutes: (int) ($data['service_minutes'] ?? $service->duration_minutes),
            promoCode: $data['promo_code'] ?? null,
        );

        return response()->json([
            'service_public_id' => $service->public_id,
            'price_breakdown' => $breakdown,
        ]);
    }
}
