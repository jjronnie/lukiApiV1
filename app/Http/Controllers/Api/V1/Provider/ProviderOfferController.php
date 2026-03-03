<?php

namespace App\Http\Controllers\Api\V1\Provider;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Provider\AcceptOfferRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProviderOfferResource;
use App\Models\Order;
use App\Models\OrderOffer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProviderOfferController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $profile = auth()->user()->providerProfile()->firstOrFail();

        $offers = OrderOffer::query()
            ->with('order')
            ->where('provider_profile_id', $profile->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->get();

        return ProviderOfferResource::collection($offers);
    }

    public function accept(AcceptOfferRequest $request, string $orderPublicId): JsonResponse
    {
        $providerProfile = $request->user()->providerProfile()->firstOrFail();

        $lock = Cache::lock('order:accept:'.$orderPublicId, 10);

        $executed = $lock->get(function () use ($providerProfile, $orderPublicId) {
            return DB::transaction(function () use ($providerProfile, $orderPublicId) {
                $order = Order::query()
                    ->where('public_id', $orderPublicId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (in_array($order->status, [OrderStatus::Accepted, OrderStatus::OnTheWay, OrderStatus::Arrived, OrderStatus::InService, OrderStatus::Completed], true)) {
                    abort(422, 'Order has already been accepted or completed.');
                }

                $offer = OrderOffer::query()
                    ->where('order_id', $order->id)
                    ->where('provider_profile_id', $providerProfile->id)
                    ->where('status', 'pending')
                    ->where('expires_at', '>', now())
                    ->lockForUpdate()
                    ->first();

                if ($offer === null) {
                    abort(422, 'Offer is not available for acceptance.');
                }

                $fromStatus = $order->status;

                $order->update([
                    'provider_profile_id' => $providerProfile->id,
                    'status' => OrderStatus::Accepted,
                    'accepted_at' => now(),
                ]);

                $offer->update([
                    'status' => 'accepted',
                    'responded_at' => now(),
                ]);

                OrderOffer::query()
                    ->where('order_id', $order->id)
                    ->where('id', '!=', $offer->id)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'expired',
                        'responded_at' => now(),
                    ]);

                $order->statusHistories()->create([
                    'from_status' => $fromStatus->value,
                    'to_status' => OrderStatus::Accepted->value,
                    'changed_by_user_id' => $providerProfile->user_id,
                    'meta' => ['source' => 'provider_offer_accept'],
                    'created_at' => now(),
                ]);

                return $order;
            });
        });

        if ($executed === false || $executed === null) {
            return response()->json(['message' => 'Order acceptance is currently locked.'], 423);
        }

        return response()->json([
            'message' => 'Offer accepted.',
            'order' => new OrderResource($executed->load(['items', 'providerProfile'])),
        ]);
    }
}
