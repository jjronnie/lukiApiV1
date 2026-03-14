<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\DispatchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchOrderOffersJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $orderId,
        public int $batchNo,
    ) {}

    public function handle(DispatchService $dispatchService): void
    {
        $order = Order::query()->find($this->orderId);
        if ($order === null) {
            return;
        }

        $dispatchService->dispatchNextBatch($order, $this->batchNo);
    }
}
