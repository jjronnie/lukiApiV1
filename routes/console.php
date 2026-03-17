<?php

use App\Enums\OrderStatus;
use App\Models\NotificationRecord;
use App\Models\Order;
use App\Services\DispatchService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orders:dispatch-scheduled', function () {
    $dispatchService = app(DispatchService::class);
    $leadHours = max(0, (int) config('luki.dispatch.scheduled_lead_hours', 5));
    $cutoff = now()->addHours($leadHours);
    $dispatched = 0;

    Order::query()
        ->where('is_scheduled', true)
        ->where('status', OrderStatus::Created)
        ->whereNotNull('scheduled_at')
        ->where('scheduled_at', '<=', $cutoff)
        ->orderBy('scheduled_at')
        ->chunkById(50, function ($orders) use (&$dispatched, $dispatchService): void {
            foreach ($orders as $order) {
                $started = DB::transaction(function () use ($order, $dispatchService): bool {
                    $freshOrder = Order::query()->lockForUpdate()->find($order->id);
                    if ($freshOrder === null
                        || ! $freshOrder->is_scheduled
                        || $freshOrder->status !== OrderStatus::Created
                        || $freshOrder->scheduled_at === null) {
                        return false;
                    }

                    $freshOrder->update([
                        'status' => OrderStatus::Offering,
                        'offering_started_at' => now(),
                    ]);

                    $freshOrder->statusHistories()->create([
                        'from_status' => OrderStatus::Created->value,
                        'to_status' => OrderStatus::Offering->value,
                        'changed_by_user_id' => $freshOrder->user_id,
                        'meta' => ['source' => 'scheduled_dispatch_start'],
                        'created_at' => now(),
                    ]);

                    $offersCreated = $dispatchService->startOrderDispatch($freshOrder);
                    if ($offersCreated === 0) {
                        $dispatchService->syncSearchState($freshOrder);
                    }

                    return true;
                });

                if ($started) {
                    $dispatched++;
                }
            }
        });

    $this->info("Scheduled dispatch processed for {$dispatched} order(s).");
})->purpose('Dispatch scheduled orders that are due for provider search');

Artisan::command('notifications:prune-old', function () {
    $retentionDays = max(1, (int) config('luki.notifications.retention_days', 30));
    $deleted = NotificationRecord::query()
        ->where('created_at', '<', now()->subDays($retentionDays))
        ->delete();

    $this->info("Deleted {$deleted} notification(s) older than {$retentionDays} days.");
})->purpose('Delete notification records older than the retention window');

Schedule::command('orders:dispatch-scheduled')->everyFiveMinutes();
Schedule::command('notifications:prune-old')->daily();
