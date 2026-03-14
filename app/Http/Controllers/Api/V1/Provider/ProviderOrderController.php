<?php

namespace App\Http\Controllers\Api\V1\Provider;

use App\Enums\MobileAppType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Provider\CancelProviderOrderRequest;
use App\Http\Requests\Api\V1\Provider\StoreProviderOrderLocationRequest;
use App\Http\Requests\Api\V1\Provider\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\CommissionService;
use App\Services\IdempotencyService;
use App\Services\NotificationDispatcher;
use App\Services\ProviderRatingService;
use App\Services\UserEmailPreferenceService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use JsonException;

class ProviderOrderController extends Controller
{
    public function __construct(
        private readonly CommissionService $commissionService,
        private readonly WalletService $walletService,
        private readonly IdempotencyService $idempotencyService,
        private readonly NotificationDispatcher $notificationDispatcher,
        private readonly ProviderRatingService $providerRatingService,
        private readonly UserEmailPreferenceService $userEmailPreferenceService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $providerProfile = $request->user()->providerProfile()->firstOrFail();
        $scope = $request->string('scope')->toString();

        $query = Order::query()
            ->where('provider_profile_id', $providerProfile->id)
            ->with(['items.addOn', 'providerProfile.user', 'providerProfile.availability', 'service.category', 'serviceTier', 'user']);

        if ($scope === 'completed') {
            $query->where('status', OrderStatus::Completed);
        } elseif ($scope === 'active') {
            $query->whereIn('status', [
                OrderStatus::Accepted,
                OrderStatus::OnTheWay,
                OrderStatus::Arrived,
                OrderStatus::InService,
            ]);
        }

        return OrderResource::collection($query->latest()->paginate(20));
    }

    public function active(Request $request): JsonResponse
    {
        $providerProfile = $request->user()->providerProfile()->firstOrFail();

        $order = Order::query()
            ->where('provider_profile_id', $providerProfile->id)
            ->whereIn('status', [
                OrderStatus::Accepted,
                OrderStatus::OnTheWay,
                OrderStatus::Arrived,
                OrderStatus::InService,
            ])
            ->with(['items.addOn', 'providerProfile.user', 'providerProfile.availability', 'service.category', 'serviceTier', 'user', 'statusHistories'])
            ->latest('accepted_at')
            ->first();

        return response()->json([
            'order' => $order === null ? null : (new OrderResource($order))->resolve(),
        ]);
    }

    public function show(Request $request, string $orderPublicId): OrderResource
    {
        $providerProfile = $request->user()->providerProfile()->firstOrFail();

        $order = Order::query()
            ->where('public_id', $orderPublicId)
            ->where('provider_profile_id', $providerProfile->id)
            ->with(['items.addOn', 'providerProfile.user', 'providerProfile.availability', 'service.category', 'serviceTier', 'user', 'statusHistories'])
            ->firstOrFail();

        return new OrderResource($order);
    }

    /**
     * @throws JsonException
     */
    public function updateStatus(UpdateOrderStatusRequest $request, string $orderPublicId): JsonResponse
    {
        $providerProfile = $request->user()->providerProfile()->with('wallet')->firstOrFail();
        $data = $request->validated();
        $idempotencyKey = $data['idempotency_key'] ?? $request->header('Idempotency-Key');
        $requestHash = hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));

        if ($idempotencyKey !== null) {
            $result = $this->idempotencyService->check($request->user(), 'orders.status.update', $idempotencyKey, $requestHash);
            if ($result['replay'] === true) {
                return response()->json($result['response_body'], $result['response_code']);
            }
        }

        $lock = Cache::lock('order:status:'.$orderPublicId, 10);

        $order = $lock->get(function () use ($providerProfile, $orderPublicId, $data, $request) {
            return DB::transaction(function () use ($providerProfile, $orderPublicId, $data, $request) {
                $order = Order::query()
                    ->where('public_id', $orderPublicId)
                    ->where('provider_profile_id', $providerProfile->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $requestedStatus = $data['status'] === 'started_working'
                    ? OrderStatus::InService
                    : OrderStatus::from($data['status']);
                $currentStatus = $order->status;

                if (! $this->isValidTransition($currentStatus, $requestedStatus)) {
                    abort(422, 'Invalid status transition.');
                }

                $changes = ['status' => $requestedStatus];

                if ($requestedStatus === OrderStatus::OnTheWay) {
                    $changes['on_the_way_at'] = now();
                }

                if ($requestedStatus === OrderStatus::Arrived) {
                    $changes['arrived_at'] = now();
                }

                if ($requestedStatus === OrderStatus::InService) {
                    $changes['in_service_at'] = now();
                    $changes['provider_eta_minutes'] = 0;
                    $changes['provider_distance_meters'] = 0;
                }

                if ($requestedStatus === OrderStatus::Completed) {
                    $changes['completed_at'] = now();
                    if (($data['mark_paid'] ?? false) === true) {
                        $changes['payment_status'] = PaymentStatus::Paid;
                        $changes['paid_at'] = now();
                    }
                }

                $order->update($changes);

                $order->statusHistories()->create([
                    'from_status' => $currentStatus->value,
                    'to_status' => $requestedStatus->value,
                    'changed_by_user_id' => $request->user()->id,
                    'meta' => [
                        'source' => 'provider_order_status_update',
                        'requested_status' => $data['status'],
                    ],
                    'created_at' => now(),
                ]);

                if ($requestedStatus === OrderStatus::Completed) {
                    $providerProfile->increment('completed_orders_count');

                    $commission = $this->commissionService->calculate($order);
                    if ($commission > 0 && $providerProfile->wallet !== null) {
                        $this->walletService->recordTransaction(
                            wallet: $providerProfile->wallet,
                            type: 'commission_deduction',
                            amount: -$commission,
                            order: $order,
                            createdByUserId: $request->user()->id,
                            reference: 'COMM-'.$order->public_id,
                            meta: ['commission_amount' => $commission],
                        );

                        $providerProfile->wallet->refresh();
                        if (! $this->walletService->canReceiveOrders($providerProfile->wallet)) {
                            $providerProfile->availability()?->update(['is_online' => false]);
                        }
                    }
                }

                return $order;
            });
        });

        if (! $order instanceof Order) {
            return response()->json(['message' => 'Order status update lock unavailable.'], 423);
        }

        $providerProfile->refresh();
        $this->providerRatingService->refresh($providerProfile);

        $responseBody = [
            'message' => 'Order status updated.',
            'order' => (new OrderResource($order->load([
                'items.addOn',
                'providerProfile.user',
                'providerProfile.availability',
                'service.category',
                'serviceTier',
                'user',
                'statusHistories',
            ])))->resolve(),
        ];

        if ($idempotencyKey !== null) {
            $this->idempotencyService->store($request->user(), 'orders.status.update', $idempotencyKey, $requestHash, 200, $responseBody);
        }

        $order->loadMissing('user');
        if ($order->status === OrderStatus::OnTheWay && $order->user !== null) {
            $this->notificationDispatcher->sendToUser(
                $order->user,
                MobileAppType::Customer,
                'provider_on_the_way',
                'Provider is on the way',
                'Your provider is on the way to your location.',
                [
                    'screen' => 'order_detail',
                    'order_id' => $order->public_id,
                ],
            );
        }

        if ($order->status === OrderStatus::Completed && $order->user !== null) {
            $preference = $this->userEmailPreferenceService->ensureForUser($order->user);

            if ($preference->booking_emails_enabled) {
                Mail::to($order->user->email)->send(new \App\Mail\CustomerBookingSummaryMail(
                    $order->loadMissing(['providerProfile.user', 'service', 'serviceTier', 'user'])
                ));
            }
        }

        return response()->json($responseBody);
    }

    public function cancel(
        CancelProviderOrderRequest $request,
        string $orderPublicId,
    ): JsonResponse {
        $providerProfile = $request->user()->providerProfile()->firstOrFail();

        $order = DB::transaction(function () use ($providerProfile, $request, $orderPublicId) {
            $order = Order::query()
                ->where('public_id', $orderPublicId)
                ->where('provider_profile_id', $providerProfile->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($order->status, [
                OrderStatus::Accepted,
                OrderStatus::OnTheWay,
                OrderStatus::Arrived,
            ], true)) {
                abort(422, 'This order cannot be cancelled anymore.');
            }

            $fromStatus = $order->status;
            $order->update([
                'status' => OrderStatus::Cancelled,
                'cancelled_at' => now(),
                'cancelled_by_user_id' => $request->user()->id,
                'cancellation_reason' => $request->validated('reason'),
            ]);

            $order->statusHistories()->create([
                'from_status' => $fromStatus->value,
                'to_status' => OrderStatus::Cancelled->value,
                'changed_by_user_id' => $request->user()->id,
                'meta' => [
                    'reason' => $request->validated('reason'),
                    'source' => 'provider_order_cancel',
                ],
                'created_at' => now(),
            ]);

            $providerProfile->increment('cancelled_orders_count');

            return $order;
        });

        $this->providerRatingService->refresh($providerProfile->fresh() ?? $providerProfile);

        $order->loadMissing('user');
        if ($order->user !== null) {
            $this->notificationDispatcher->sendToUser(
                $order->user,
                MobileAppType::Customer,
                'provider_cancelled_order',
                'Provider cancelled the order',
                'Your provider cancelled this booking. Please review the latest status in the app.',
                [
                    'screen' => 'order_detail',
                    'order_id' => $order->public_id,
                ],
            );
        }

        return response()->json([
            'message' => 'Order cancelled.',
            'order' => new OrderResource($order->load([
                'items.addOn',
                'providerProfile.user',
                'providerProfile.availability',
                'service.category',
                'serviceTier',
                'user',
                'statusHistories',
            ])),
        ]);
    }

    public function updateLocation(
        StoreProviderOrderLocationRequest $request,
        string $orderPublicId,
    ): JsonResponse {
        $providerProfile = $request->user()->providerProfile()->firstOrFail();
        $data = $request->validated();

        $order = Order::query()
            ->where('public_id', $orderPublicId)
            ->where('provider_profile_id', $providerProfile->id)
            ->whereIn('status', [
                OrderStatus::Accepted,
                OrderStatus::OnTheWay,
                OrderStatus::Arrived,
            ])
            ->firstOrFail();

        $providerProfile->locations()->create([
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'accuracy_meters' => $data['accuracy_meters'] ?? null,
            'heading' => $data['heading'] ?? null,
            'speed' => $data['speed'] ?? null,
            'source' => $data['source'] ?? 'active_order',
            'recorded_at' => now(),
        ]);

        $providerProfile->availability()?->update([
            'last_seen_at' => now(),
        ]);

        $distanceKm = $this->distanceKm(
            (float) $data['lat'],
            (float) $data['lng'],
            (float) $order->location_lat,
            (float) $order->location_lng,
        );
        $travelSpeed = max(10, (int) config('luki.tracking.default_travel_speed_kph', 25));
        $etaMinutes = (int) max(1, round(($distanceKm / $travelSpeed) * 60));

        $order->update([
            'provider_last_location_lat' => $data['lat'],
            'provider_last_location_lng' => $data['lng'],
            'provider_last_location_at' => now(),
            'provider_eta_minutes' => $etaMinutes,
            'provider_distance_meters' => (int) round($distanceKm * 1000),
        ]);

        return response()->json([
            'message' => 'Provider location updated.',
            'tracking' => [
                'provider_lat' => $order->provider_last_location_lat,
                'provider_lng' => $order->provider_last_location_lng,
                'provider_location_at' => $order->provider_last_location_at,
                'provider_eta_minutes' => $order->provider_eta_minutes,
                'provider_distance_meters' => $order->provider_distance_meters,
            ],
        ]);
    }

    private function isValidTransition(OrderStatus $current, OrderStatus $next): bool
    {
        $allowed = [
            OrderStatus::Accepted->value => [OrderStatus::OnTheWay],
            OrderStatus::OnTheWay->value => [OrderStatus::Arrived, OrderStatus::InService],
            OrderStatus::Arrived->value => [OrderStatus::InService],
            OrderStatus::InService->value => [OrderStatus::Completed],
        ];

        return in_array($next, $allowed[$current->value] ?? [], true);
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
}
