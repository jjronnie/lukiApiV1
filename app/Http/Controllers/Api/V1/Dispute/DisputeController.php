<?php

namespace App\Http\Controllers\Api\V1\Dispute;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Dispute\StoreDisputeRequest;
use App\Models\Dispute;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class DisputeController extends Controller
{
    public function store(StoreDisputeRequest $request): JsonResponse
    {
        $data = $request->validated();

        $order = Order::query()
            ->where('public_id', $data['order_public_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $dispute = Dispute::query()->create([
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
            'category' => $data['category'],
            'description' => $data['description'],
            'attachments' => $data['attachments'] ?? null,
            'status' => 'open',
        ]);

        return response()->json([
            'message' => 'Dispute created.',
            'dispute' => [
                'status' => $dispute->status,
                'created_at' => $dispute->created_at,
            ],
        ], 201);
    }
}
