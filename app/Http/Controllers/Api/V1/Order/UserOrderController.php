<?php

namespace App\Http\Controllers\Api\V1\Order;

use App\Enums\OrderBookingMode;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Order\CancelOrderRequest;
use App\Http\Requests\Api\V1\Order\PairProviderPreviewRequest;
use App\Http\Requests\Api\V1\Order\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProviderPreviewResource;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceTier;
use App\Services\DispatchService;
use App\Services\IdempotencyService;
use App\Services\NotificationDispatcher;
use App\Services\PriceEstimateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserOrderController extends Controller
{
    public function __construct(
        private readonly PriceEstimateService $priceEstimateService,
        private readonly DispatchService $dispatchService,
        private readonly IdempotencyService $idempotencyService,
        private readonly NotificationDispatcher $notificationDispatcher,
    ) {}

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        $idempotencyKey = $data['idempotency_key'] ?? $request->header('Idempotency-Key');
        $requestHash = hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));

        if ($idempotencyKey !== null) {
            $idempotentResult = $this->idempotencyService->check($request->user(), 'orders.create', $idempotencyKey, $requestHash);
            if ($idempotentResult['replay'] === true) {
                return response()->json($idempotentResult['response_body'], $idempotentResult['response_code']);
            }
        }

        $service = Service::query()
            ->where('public_id', $data['service_public_id'])
            ->where('is_active', true)
            ->firstOrFail();

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

        $pairProvider = null;
        if (($data['booking_mode'] ?? OrderBookingMode::Normal->value) === OrderBookingMode::Pair->value) {
            $providerNumber = (int) ($data['pair_provider_number'] ?? 0);
            $resolution = $this->dispatchService->resolvePairProvider($service, $serviceTier, $providerNumber);

            if ($resolution['provider'] === null) {
                throw ValidationException::withMessages([
                    'pair_provider_number' => [$resolution['message'] ?? 'Provider pairing failed.'],
                ]);
            }

            $pairProvider = $resolution['provider'];
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

        $order = DB::transaction(function () use ($request, $data, $service, $serviceTier, $addOnIds, $breakdown) {
            $order = Order::query()->create([
                'user_id' => $request->user()->id,
                'service_id' => $service->id,
                'service_tier_id' => $serviceTier->id,
                'transport_zone_id' => $breakdown['transport_zone_id'] ?? null,
                'service_name_snapshot' => $service->name,
                'service_tier_name_snapshot' => $serviceTier->name,
                'transport_zone_name_snapshot' => $breakdown['transport_zone_name'] ?? null,
                'status' => OrderStatus::Created,
                'booking_mode' => $data['booking_mode'],
                'pair_provider_number' => ($data['booking_mode'] ?? OrderBookingMode::Normal->value) === OrderBookingMode::Pair->value
                    ? (int) ($data['pair_provider_number'] ?? 0)
                    : null,
                'is_scheduled' => $data['is_scheduled'],
                'scheduled_at' => $data['is_scheduled'] ? $data['scheduled_at'] : null,
                'address_text' => $data['address_text'],
                'location_lat' => $data['location_lat'],
                'location_lng' => $data['location_lng'],
                'place_id' => $data['place_id'] ?? null,
                'payment_method' => $data['payment_method'],
                'payment_status' => PaymentStatus::Unpaid,
                'subtotal_amount' => $breakdown['subtotal_amount'],
                'transport_fee_amount' => $breakdown['transport_fee_amount'],
                'distance_fee_amount' => $breakdown['distance_fee_amount'],
                'overtime_fee_amount' => $breakdown['overtime_fee_amount'],
                'peak_fee_amount' => $breakdown['peak_fee_amount'],
                'tax_amount' => $breakdown['tax_amount'],
                'discount_amount' => $breakdown['discount_amount'],
                'total_amount' => $breakdown['total_amount'],
                'price_breakdown' => $breakdown,
                'promo_code' => $data['promo_code'] ?? null,
            ]);

            $order->items()->create([
                'item_type' => 'service',
                'service_id' => $service->id,
                'name_snapshot' => $service->name,
                'service_tier_id' => $serviceTier->id,
                'tier_name_snapshot' => $serviceTier->name,
                'unit_price_amount' => $serviceTier->price_amount,
                'quantity' => 1,
                'line_total_amount' => $serviceTier->price_amount,
            ]);

            $addOns = $service->addOns()->whereIn('id', $addOnIds)->where('is_active', true)->get();
            foreach ($addOns as $addOn) {
                $order->items()->create([
                    'item_type' => 'add_on',
                    'add_on_id' => $addOn->id,
                    'name_snapshot' => $addOn->name,
                    'unit_price_amount' => $addOn->price_amount,
                    'quantity' => 1,
                    'line_total_amount' => $addOn->price_amount,
                ]);
            }

            $order->statusHistories()->create([
                'from_status' => null,
                'to_status' => OrderStatus::Created->value,
                'changed_by_user_id' => $request->user()->id,
                'meta' => ['source' => 'user_order_create'],
                'created_at' => now(),
            ]);

            return $order;
        });

        $isPairBooking = (($data['booking_mode'] ?? OrderBookingMode::Normal->value) === OrderBookingMode::Pair->value);

        if ($isPairBooking || ! $order->is_scheduled) {
            $fromStatus = $order->status;
            $order->update([
                'status' => OrderStatus::Offering,
                'offering_started_at' => now(),
            ]);

            $order->statusHistories()->create([
                'from_status' => $fromStatus->value,
                'to_status' => OrderStatus::Offering->value,
                'changed_by_user_id' => $request->user()->id,
                'meta' => [
                    'source' => $isPairBooking ? 'pair_dispatch_start' : 'dispatch_start',
                ],
                'created_at' => now(),
            ]);

            if ($isPairBooking && $pairProvider !== null) {
                $this->dispatchService->offerPair(
                    $order,
                    $pairProvider,
                    (int) config('luki.dispatch.offer_expiry_seconds', 30),
                );
            } else {
                $offersCreated = $this->dispatchService->startOrderDispatch($order);

                if ($offersCreated === 0) {
                    $order = $this->dispatchService->syncSearchState($order);
                }
            }
        }

        $order = $this->dispatchService->syncSearchState($order);

        $responseBody = [
            'message' => 'Order created.',
            'order' => (new OrderResource($order->load([
                'items',
                'statusHistories',
                'providerProfile.user',
                'providerProfile.availability',
                'service.category',
                'serviceTier',
                'items.addOn',
            ])))->resolve(),
        ];

        if ($idempotencyKey !== null) {
            $this->idempotencyService->store($request->user(), 'orders.create', $idempotencyKey, $requestHash, 201, $responseBody);
        }

        return response()->json($responseBody, 201);
    }

    public function index(Request $request)
    {
        if ($request->boolean('activity_view')) {
            return $this->activityIndex($request);
        }

        $orders = Order::query()
            ->where('user_id', auth()->id())
            ->where(function ($query) {
                $query->where('status', '!=', OrderStatus::Expired->value)
                    ->orWhereNull('cancellation_reason')
                    ->orWhere('cancellation_reason', '!=', 'failed_to_find_provider');
            })
            ->with(['items.addOn', 'providerProfile.user', 'providerProfile.availability', 'service.category', 'serviceTier'])
            ->latest()
            ->paginate(20);

        $orders->getCollection()->transform(fn (Order $order) => $this->dispatchService->syncSearchState($order));

        return OrderResource::collection($orders);
    }

    private function activityIndex(Request $request): JsonResponse
    {
        $limitPerSection = max(1, min((int) $request->integer('limit_per_section', 5), 10));
        $relations = ['items.addOn', 'providerProfile.user', 'providerProfile.availability', 'service.category', 'serviceTier'];
        $ongoingStatuses = [
            OrderStatus::Created,
            OrderStatus::Offering,
            OrderStatus::Accepted,
            OrderStatus::OnTheWay,
            OrderStatus::Arrived,
            OrderStatus::InService,
        ];

        $ongoing = Order::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', array_map(fn (OrderStatus $status) => $status->value, $ongoingStatuses))
            ->with($relations)
            ->latest()
            ->limit($limitPerSection)
            ->get()
            ->map(fn (Order $order) => $this->dispatchService->syncSearchState($order))
            ->values();

        $completed = Order::query()
            ->where('user_id', $request->user()->id)
            ->where('status', OrderStatus::Completed)
            ->with($relations)
            ->latest()
            ->limit($limitPerSection)
            ->get()
            ->map(fn (Order $order) => $this->dispatchService->syncSearchState($order))
            ->values();

        return response()->json([
            'ongoing' => OrderResource::collection($ongoing)->resolve(),
            'completed' => OrderResource::collection($completed)->resolve(),
        ]);
    }

    public function show(string $publicId): OrderResource
    {
        $order = Order::query()
            ->where('public_id', $publicId)
            ->where('user_id', auth()->id())
            ->with(['items.addOn', 'providerProfile.user', 'providerProfile.availability', 'statusHistories', 'service.category', 'serviceTier', 'offers'])
            ->firstOrFail();

        $order = $this->dispatchService->syncSearchState($order);

        return new OrderResource($order);
    }

    public function previewPairProvider(PairProviderPreviewRequest $request): JsonResponse
    {
        $data = $request->validated();

        $service = Service::query()
            ->where('public_id', $data['service_public_id'])
            ->where('is_active', true)
            ->firstOrFail();

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

        $resolution = $this->dispatchService->resolvePairProvider(
            $service,
            $serviceTier,
            (int) $data['provider_number'],
        );

        if ($resolution['provider'] === null) {
            throw ValidationException::withMessages([
                'provider_number' => [$resolution['message'] ?? 'Provider pairing failed.'],
            ]);
        }

        return response()->json([
            'message' => 'Provider verified.',
            'provider' => (new ProviderPreviewResource(
                $resolution['provider']->loadMissing('user', 'availability')
            ))->resolve(),
        ]);
    }

    public function cancel(CancelOrderRequest $request, string $publicId): JsonResponse
    {
        $order = Order::query()
            ->where('public_id', $publicId)
            ->where('user_id', $request->user()->id)
            ->with(['providerProfile.user', 'service.category', 'serviceTier'])
            ->firstOrFail();

        if (in_array($order->status, [OrderStatus::Completed, OrderStatus::Cancelled, OrderStatus::Expired, OrderStatus::InService], true)) {
            return response()->json(['message' => 'Order cannot be cancelled in current status.'], 422);
        }

        if (in_array($order->status, [OrderStatus::Accepted, OrderStatus::OnTheWay, OrderStatus::Arrived], true)
            && blank($request->validated('reason'))) {
            return response()->json([
                'message' => 'Please provide a cancellation reason for an accepted order.',
            ], 422);
        }

        $cancelFee = 0;
        if ($order->status === OrderStatus::OnTheWay) {
            $cancelFee = (int) config('luki.cancellation_fee_amount', 2000);
        }

        if ($order->status === OrderStatus::Accepted && $order->accepted_at !== null && $order->accepted_at->diffInMinutes(now()) > 2) {
            $cancelFee = (int) config('luki.cancellation_fee_amount', 2000);
        }

        $fromStatus = $order->status;
        $order->update([
            'status' => OrderStatus::Cancelled,
            'cancelled_at' => now(),
            'cancelled_by_user_id' => $request->user()->id,
            'cancellation_reason' => $request->validated('reason'),
            'cancellation_fee_amount' => $cancelFee,
        ]);

        if ($order->providerProfile !== null) {
            $order->providerProfile->increment('cancelled_orders_count');
        }

        $order->statusHistories()->create([
            'from_status' => $fromStatus->value,
            'to_status' => OrderStatus::Cancelled->value,
            'changed_by_user_id' => $request->user()->id,
            'meta' => [
                'reason' => $request->validated('reason'),
                'cancellation_fee_amount' => $cancelFee,
            ],
            'created_at' => now(),
        ]);

        if ($order->providerProfile?->user !== null) {
            $this->notificationDispatcher->sendToUser(
                $order->providerProfile->user,
                \App\Enums\MobileAppType::Provider,
                'order_cancelled',
                'Order cancelled',
                'A customer cancelled an order that was assigned to you.',
                [
                    'screen' => 'provider_orders',
                    'order_id' => $order->public_id,
                ],
                $order->providerProfile,
            );
        }

        return response()->json([
            'message' => 'Order cancelled.',
            'order' => new OrderResource($order->load(['items', 'providerProfile', 'statusHistories', 'service.category', 'serviceTier'])),
        ]);
    }
}
