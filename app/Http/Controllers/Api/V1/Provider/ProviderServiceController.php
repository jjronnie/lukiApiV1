<?php

namespace App\Http\Controllers\Api\V1\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Provider\SyncProviderServicesRequest;
use App\Models\Service;
use Illuminate\Http\JsonResponse;

class ProviderServiceController extends Controller
{
    public function sync(SyncProviderServicesRequest $request): JsonResponse
    {
        $profile = $request->user()->providerProfile()->firstOrFail();

        $serviceIds = Service::query()
            ->whereIn('public_id', $request->validated('service_public_ids'))
            ->pluck('id')
            ->all();

        $syncData = collect($serviceIds)->mapWithKeys(fn (int $id) => [$id => ['is_active' => true]])->all();
        $profile->services()->sync($syncData);

        return response()->json([
            'message' => 'Provider services updated.',
            'service_count' => count($serviceIds),
        ]);
    }
}
