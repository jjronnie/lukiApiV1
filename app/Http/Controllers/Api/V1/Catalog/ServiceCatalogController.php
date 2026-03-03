<?php

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceDetailResource;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceCatalogController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $services = Service::query()
            ->where('is_active', true)
            ->with(['addOns' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return ServiceResource::collection($services);
    }

    public function show(string $publicId): ServiceDetailResource
    {
        $service = Service::query()
            ->where('public_id', $publicId)
            ->where('is_active', true)
            ->with([
                'addOns' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                'pricingRules' => fn ($query) => $query->where('is_active', true)->orderBy('priority'),
            ])
            ->firstOrFail();

        return new ServiceDetailResource($service);
    }

    public function addons(string $publicId): JsonResponse
    {
        $service = Service::query()->where('public_id', $publicId)->where('is_active', true)->firstOrFail();

        $addOns = $service->addOns()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['public_id', 'name', 'description', 'price_amount']);

        return response()->json([
            'service_public_id' => $service->public_id,
            'addons' => $addOns,
        ]);
    }
}
