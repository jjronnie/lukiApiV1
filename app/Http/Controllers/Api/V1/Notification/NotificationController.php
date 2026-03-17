<?php

namespace App\Http\Controllers\Api\V1\Notification;

use App\Enums\MobileAppType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Notification\TestNotificationRequest;
use App\Http\Resources\NotificationRecordResource;
use App\Models\NotificationRecord;
use App\Services\NotificationDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationDispatcher $notificationDispatcher,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $notifications = NotificationRecord::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'notifications' => NotificationRecordResource::collection(
                $notifications->getCollection()
            )->resolve(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    public function markRead(Request $request, NotificationRecord $notificationRecord): JsonResponse
    {
        abort_unless($notificationRecord->user_id === $request->user()->id, 404);

        if ($notificationRecord->read_at === null) {
            $notificationRecord->forceFill(['read_at' => now()])->save();
        }

        return response()->json([
            'message' => 'Notification marked as read.',
            'notification' => new NotificationRecordResource($notificationRecord),
        ]);
    }

    public function destroy(Request $request, NotificationRecord $notificationRecord): JsonResponse
    {
        abort_unless($notificationRecord->user_id === $request->user()->id, 404);

        $notificationRecord->delete();

        return response()->json([
            'message' => 'Notification cleared.',
        ]);
    }

    public function test(TestNotificationRequest $request): JsonResponse
    {
        $appType = MobileAppType::from($request->validated('app_type'));

        if (! $appType->allows($request->user())) {
            return response()->json([
                'message' => $appType->mismatchMessage(),
            ], 403);
        }

        $result = $this->notificationDispatcher->sendToUser(
            $request->user(),
            $appType,
            'test',
            $request->validated('title') ?: 'Test notification',
            $request->validated('body') ?: 'Push notifications are working.',
            [
                'screen' => 'notifications',
            ],
            $request->user()->providerProfile,
        );

        return response()->json([
            'message' => 'Test notification dispatched.',
            'sent' => $result['sent'],
            'failed' => $result['failed'],
            'notification' => new NotificationRecordResource($result['record']),
        ]);
    }
}
