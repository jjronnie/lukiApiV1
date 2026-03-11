<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTransportZoneRequest;
use App\Http\Requests\Admin\UpdateTransportZoneRequest;
use App\Models\TransportZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransportZoneController extends Controller
{
    public function index(): View
    {
        return view('admin.transport-zones.index', [
            'zones' => TransportZone::query()
                ->orderByDesc('is_fallback')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.transport-zones.create');
    }

    public function store(StoreTransportZoneRequest $request): RedirectResponse
    {
        $zone = TransportZone::query()->create($request->validated());
        $this->normalizeFallbackZone($zone);

        return redirect()->route('admin.transport-zones.index')->with('status', 'Transport zone created.');
    }

    public function show(TransportZone $transport_zone): View
    {
        return view('admin.transport-zones.show', [
            'zone' => $transport_zone,
        ]);
    }

    public function edit(TransportZone $transport_zone): View
    {
        return view('admin.transport-zones.edit', [
            'zone' => $transport_zone,
        ]);
    }

    public function update(UpdateTransportZoneRequest $request, TransportZone $transport_zone): RedirectResponse
    {
        $transport_zone->update($request->validated());
        $this->normalizeFallbackZone($transport_zone);

        return redirect()->route('admin.transport-zones.index')->with('status', 'Transport zone updated.');
    }

    public function destroy(TransportZone $transport_zone): RedirectResponse
    {
        $transport_zone->delete();

        return redirect()->route('admin.transport-zones.index')->with('status', 'Transport zone deleted.');
    }

    private function normalizeFallbackZone(TransportZone $zone): void
    {
        if (! $zone->is_fallback) {
            return;
        }

        DB::transaction(function () use ($zone): void {
            TransportZone::query()
                ->whereKeyNot($zone->id)
                ->update(['is_fallback' => false]);

            $zone->refresh();
        });
    }
}
