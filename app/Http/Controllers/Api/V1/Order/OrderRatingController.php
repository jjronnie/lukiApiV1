<?php

namespace App\Http\Controllers\Api\V1\Order;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Order\RateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\ProviderRatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderRatingController extends Controller
{
    public function __construct(
        private readonly ProviderRatingService $providerRatingService,
    ) {}

    public function store(RateOrderRequest $request, string $publicId): JsonResponse
    {
        $data = $request->validated();

        $order = Order::query()
            ->where('public_id', $publicId)
            ->where('user_id', $request->user()->id)
            ->with('providerProfile')
            ->firstOrFail();

        if ($order->status !== OrderStatus::Completed) {
            return response()->json(['message' => 'Only completed orders can be rated.'], 422);
        }

        if ($order->provider_rating !== null) {
            return response()->json(['message' => 'Order already rated.'], 422);
        }

        DB::transaction(function () use ($order, $data): void {
            $order->update([
                'provider_rating' => $data['rating'],
                'provider_review' => $data['review'] ?? null,
                'rated_at' => now(),
            ]);
        });

        if ($order->providerProfile !== null) {
            $this->providerRatingService->refresh($order->providerProfile->fresh() ?? $order->providerProfile);
        }

        return response()->json([
            'message' => 'Rating submitted.',
            'order' => new OrderResource($order->fresh(['items', 'providerProfile', 'service.category', 'serviceTier'])),
        ]);
    }
}
