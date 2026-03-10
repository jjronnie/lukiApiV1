<?php

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceDetailResource;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceCatalogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $services = Service::query()
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->whereHas('tiers', fn ($query) => $query->where('is_active', true))
            ->with([
                'category',
                'addOns' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                'tiers' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('price_amount'),
            ])
            ->when(
                $request->boolean('featured'),
                fn ($query) => $query->where('is_featured', true)
            )
            ->when(
                $request->filled('category_slug'),
                fn ($query) => $query->whereHas(
                    'category',
                    fn ($categoryQuery) => $categoryQuery->where('slug', $request->string('category_slug')->toString())
                )
            )
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->when(
                $request->integer('limit') > 0,
                fn ($query) => $query->limit(min(20, max(1, $request->integer('limit'))))
            )
            ->get();

        return ServiceResource::collection($services);
    }

    public function show(string $publicId): ServiceDetailResource
    {
        $service = Service::query()
            ->where('public_id', $publicId)
            ->where('is_active', true)
            ->with([
                'category',
                'addOns' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                'pricingRules' => fn ($query) => $query->where('is_active', true)->orderBy('priority'),
                'tiers' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('price_amount'),
            ])
            ->firstOrFail();

        return new ServiceDetailResource($service);
    }

    public function addons(string $publicId): JsonResponse
    {
        $service = Service::query()
            ->where('public_id', $publicId)
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->firstOrFail();

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
