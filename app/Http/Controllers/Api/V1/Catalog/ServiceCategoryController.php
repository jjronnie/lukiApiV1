<?php

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceCategoryResource;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceCategoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(50, max(1, $request->integer('per_page') ?: $request->integer('limit') ?: 20));

        $categories = ServiceCategory::query()
            ->where('is_active', true)
            ->whereHas('services', fn ($query) => $query
                ->where('is_active', true)
                ->whereHas('tiers', fn ($tierQuery) => $tierQuery->where('is_active', true)))
            ->withCount(['services' => fn ($query) => $query
                ->where('is_active', true)
                ->whereHas('tiers', fn ($tierQuery) => $tierQuery->where('is_active', true))])
            ->when(
                $request->boolean('featured'),
                fn ($query) => $query->where('is_featured', true)
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return ServiceCategoryResource::collection($categories);
    }

    public function show(string $slug): ServiceCategoryResource
    {
        $category = ServiceCategory::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->whereHas('services', fn ($query) => $query
                ->where('is_active', true)
                ->whereHas('tiers', fn ($tierQuery) => $tierQuery->where('is_active', true)))
            ->withCount(['services' => fn ($query) => $query
                ->where('is_active', true)
                ->whereHas('tiers', fn ($tierQuery) => $tierQuery->where('is_active', true))])
            ->firstOrFail();

        return new ServiceCategoryResource($category);
    }
}
