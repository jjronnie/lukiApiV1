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
        $categories = ServiceCategory::query()
            ->where('is_active', true)
            ->withCount(['services' => fn ($query) => $query
                ->where('is_active', true)
                ->whereHas('tiers', fn ($tierQuery) => $tierQuery->where('is_active', true))])
            ->having('services_count', '>', 0)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->when(
                $request->integer('limit') > 0,
                fn ($query) => $query->limit(min(50, max(1, $request->integer('limit'))))
            )
            ->get();

        return ServiceCategoryResource::collection($categories);
    }
}
