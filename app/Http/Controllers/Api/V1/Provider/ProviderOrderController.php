<?php

namespace App\Http\Controllers\Api\V1\Provider;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Provider\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\CommissionService;
use App\Services\IdempotencyService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use JsonException;

class ProviderOrderController extends Controller
{
    public function __construct(
        private readonly CommissionService $commissionService,
        private readonly WalletService $walletService,
        private readonly IdempotencyService $idempotencyService,
    ) {}

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

                $newStatus = OrderStatus::from($data['status']);
                $currentStatus = $order->status;

                if (! $this->isValidTransition($currentStatus, $newStatus)) {
                    abort(422, 'Invalid status transition.');
                }

                $changes = ['status' => $newStatus];

                if ($newStatus === OrderStatus::OnTheWay) {
                    $changes['on_the_way_at'] = now();
                }

                if ($newStatus === OrderStatus::Arrived) {
                    $changes['arrived_at'] = now();
                }

                if ($newStatus === OrderStatus::InService) {
                    $changes['in_service_at'] = now();
                }

                if ($newStatus === OrderStatus::Completed) {
                    $changes['completed_at'] = now();
                    if (($data['mark_paid'] ?? false) === true) {
                        $changes['payment_status'] = PaymentStatus::Paid;
                        $changes['paid_at'] = now();
                    }
                }

                $order->update($changes);

                $order->statusHistories()->create([
                    'from_status' => $currentStatus->value,
                    'to_status' => $newStatus->value,
                    'changed_by_user_id' => $request->user()->id,
                    'meta' => ['source' => 'provider_order_status_update'],
                    'created_at' => now(),
                ]);

                if ($newStatus === OrderStatus::Completed) {
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

        $responseBody = [
            'message' => 'Order status updated.',
            'order' => (new OrderResource($order->load(['items', 'providerProfile'])))->resolve(),
        ];

        if ($idempotencyKey !== null) {
            $this->idempotencyService->store($request->user(), 'orders.status.update', $idempotencyKey, $requestHash, 200, $responseBody);
        }

        return response()->json($responseBody);
    }

    private function isValidTransition(OrderStatus $current, OrderStatus $next): bool
    {
        $allowed = [
            OrderStatus::Accepted->value => [OrderStatus::OnTheWay],
            OrderStatus::OnTheWay->value => [OrderStatus::Arrived],
            OrderStatus::Arrived->value => [OrderStatus::InService],
            OrderStatus::InService->value => [OrderStatus::Completed],
        ];

        return in_array($next, $allowed[$current->value] ?? [], true);
    }
}
