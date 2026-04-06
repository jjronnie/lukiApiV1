<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function index(): View
    {
        return view('admin.services.index', [
            'services' => Service::query()
                ->with('category')
                ->withCount(['tiers' => fn ($query) => $query->where('is_active', true)])
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.create', [
            'categories' => ServiceCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data): void {
            $service = Service::query()->create([
                ...Arr::except($data, ['tiers']),
                'base_price_amount' => $this->minimumTierPrice($data['tiers']),
            ]);

            $this->syncTiers($service, $data['tiers']);
        });

        return redirect()->route('admin.services.index')->with('status', 'Service created.');
    }

    public function show(Service $service): View
    {
        return view('admin.services.show', [
            'service' => $service->load(['category', 'addOns', 'pricingRules', 'tiers']),
        ]);
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', [
            'service' => $service->load(['category', 'tiers']),
            'categories' => ServiceCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($service, $data): void {
            $service->update([
                ...Arr::except($data, ['tiers']),
                'base_price_amount' => $this->minimumTierPrice($data['tiers']),
            ]);

            $this->syncTiers($service, $data['tiers']);
        });

        $this->auditLogService->log(
            action: AuditAction::ServiceEdited->value,
            actor: $request->user(),
            auditableType: Service::class,
            auditableId: $service->id,
            meta: ['service_public_id' => $service->public_id],
            request: $request,
        );

        return redirect()->route('admin.services.index')->with('status', 'Service updated.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()->route('admin.services.index')->with('status', 'Service deleted.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $tiers
     */
    private function syncTiers(Service $service, array $tiers): void
    {
        $existingTiers = $service->tiers()->get()->keyBy('id');
        $keptTierIds = [];

        foreach ($tiers as $tierData) {
            $payload = Arr::except($tierData, ['id']);
            $tierId = $tierData['id'] ?? null;

            if ($tierId !== null && $existingTiers->has($tierId)) {
                $existingTiers->get($tierId)?->update($payload);
                $keptTierIds[] = (int) $tierId;

                continue;
            }

            $createdTier = $service->tiers()->create($payload);
            $keptTierIds[] = $createdTier->id;
        }

        $service->tiers()
            ->whereNotIn('id', $keptTierIds === [] ? [-1] : $keptTierIds)
            ->delete();

        $service->refreshBasePriceFromTiers();
    }

    /**
     * @param  array<int, array<string, mixed>>  $tiers
     */
    private function minimumTierPrice(array $tiers): int
    {
        return (int) max(0, collect($tiers)->min(fn (array $tier) => (int) ($tier['price_amount'] ?? 0)) ?? 0);
    }
}
