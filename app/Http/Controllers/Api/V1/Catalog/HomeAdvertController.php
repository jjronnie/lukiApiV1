<?php

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Resources\HomeAdvertResource;
use App\Models\HomeAdvert;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HomeAdvertController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $adverts = HomeAdvert::query()
            ->visible()
            ->orderBy('sort_order')
            ->orderByDesc('updated_at')
            ->when(
                $request->integer('limit') > 0,
                fn ($query) => $query->limit(min(20, max(1, $request->integer('limit'))))
            )
            ->get();

        return HomeAdvertResource::collection($adverts);
    }
}
