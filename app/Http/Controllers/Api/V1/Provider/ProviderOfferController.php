<?php

namespace App\Http\Controllers\Api\V1\Provider;

use App\Enums\MobileAppType;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Provider\AcceptOfferRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProviderOfferResource;
use App\Models\Order;
use App\Models\OrderOffer;
use App\Services\DispatchService;
use App\Services\NotificationDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProviderOfferController extends Controller
{
    public function __construct(
        private readonly DispatchService $dispatchService,
        private readonly NotificationDispatcher $notificationDispatcher,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $profile = auth()->user()->providerProfile()->firstOrFail();

        $hasOngoingOrder = Order::query()
            ->where('provider_profile_id', $profile->id)
            ->whereIn('status', Order::providerBusyStatusValues())
            ->exists();

        if ($hasOngoingOrder) {
            return ProviderOfferResource::collection(collect());
        }

        $offers = OrderOffer::query()
            ->with(['order.user', 'order.service.category', 'order.serviceTier'])
            ->where('provider_profile_id', $profile->id)
            ->where(function ($query) {
                $query->where(function ($pendingQuery) {
                    $pendingQuery
                        ->where('status', 'pending')
                        ->where('expires_at', '>', now());
                })->orWhere(function ($takenQuery) {
                    $takenQuery
                        ->where('status', 'taken')
                        ->where('responded_at', '>=', now()->subSeconds(
                            (int) config('luki.dispatch.taken_visibility_seconds', 45)
                        ));
                });
            })
            ->latest('created_at')
            ->get();

        return ProviderOfferResource::collection($offers);
    }

    public function skip(string $orderPublicId): JsonResponse
    {
        $providerProfile = auth()->user()->providerProfile()->firstOrFail();

        $offer = OrderOffer::query()
            ->whereHas('order', fn ($query) => $query->where('public_id', $orderPublicId))
            ->where('provider_profile_id', $providerProfile->id)
            ->where('status', 'pending')
            ->first();

        if ($offer === null) {
            return response()->json(['message' => 'Offer is no longer available.'], 422);
        }

        $offer->update([
            'status' => 'skipped',
            'responded_at' => now(),
        ]);

        $this->dispatchService->refillPendingOffers($offer->order);

        return response()->json(['message' => 'Offer skipped.']);
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

                $hasOngoingOrder = Order::query()
                    ->where('provider_profile_id', $providerProfile->id)
                    ->where('id', '!=', $order->id)
                    ->whereIn('status', Order::providerBusyStatusValues())
                    ->exists();

                if ($hasOngoingOrder) {
                    abort(422, 'Finish your ongoing order before accepting a new one.');
                }

                if (in_array($order->status, [OrderStatus::Accepted, OrderStatus::OnTheWay, OrderStatus::Arrived, OrderStatus::InService, OrderStatus::Completed], true)) {
                    OrderOffer::query()
                        ->where('order_id', $order->id)
                        ->where('provider_profile_id', $providerProfile->id)
                        ->where('status', 'pending')
                        ->update([
                            'status' => 'taken',
                            'responded_at' => now(),
                        ]);

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
                        'status' => 'taken',
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

        $executed->loadMissing('user');
        if ($executed->user !== null) {
            $this->notificationDispatcher->sendToUser(
                $executed->user,
                MobileAppType::Customer,
                'order_matched',
                'Provider matched',
                'A provider accepted your booking request.',
                [
                    'screen' => 'order_detail',
                    'order_id' => $executed->public_id,
                ],
            );
        }

        return response()->json([
            'message' => 'Offer accepted.',
            'order' => new OrderResource($executed->load(['items', 'providerProfile.user', 'service.category', 'serviceTier', 'user'])),
        ]);
    }
}
