<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHomeAdvertRequest;
use App\Http\Requests\Admin\UpdateHomeAdvertRequest;
use App\Models\HomeAdvert;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeAdvertController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function index(): View
    {
        return view('admin.home-adverts.index', [
            'adverts' => HomeAdvert::query()
                ->orderBy('sort_order')
                ->orderByDesc('updated_at')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.home-adverts.create');
    }

    public function store(StoreHomeAdvertRequest $request): RedirectResponse
    {
        $advert = HomeAdvert::query()->create($request->validated());

        $this->auditLogService->log(
            action: AuditAction::HomeAdvertChanged->value,
            actor: $request->user(),
            auditableType: HomeAdvert::class,
            auditableId: $advert->id,
            meta: ['home_advert_public_id' => $advert->public_id, 'operation' => 'created'],
            request: $request,
        );

        return redirect()->route('admin.home-adverts.index')->with('status', 'Home advert created.');
    }

    public function show(HomeAdvert $home_advert): View
    {
        return view('admin.home-adverts.show', [
            'advert' => $home_advert,
        ]);
    }

    public function edit(HomeAdvert $home_advert): View
    {
        return view('admin.home-adverts.edit', [
            'advert' => $home_advert,
        ]);
    }

    public function update(UpdateHomeAdvertRequest $request, HomeAdvert $home_advert): RedirectResponse
    {
        $home_advert->update($request->validated());

        $this->auditLogService->log(
            action: AuditAction::HomeAdvertChanged->value,
            actor: $request->user(),
            auditableType: HomeAdvert::class,
            auditableId: $home_advert->id,
            meta: ['home_advert_public_id' => $home_advert->public_id, 'operation' => 'updated'],
            request: $request,
        );

        return redirect()->route('admin.home-adverts.index')->with('status', 'Home advert updated.');
    }

    public function destroy(HomeAdvert $home_advert): RedirectResponse
    {
        $home_advert->delete();

        return redirect()->route('admin.home-adverts.index')->with('status', 'Home advert deleted.');
    }
}
