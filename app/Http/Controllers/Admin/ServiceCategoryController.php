<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceCategoryRequest;
use App\Http\Requests\Admin\UpdateServiceCategoryRequest;
use App\Models\ServiceCategory;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceCategoryController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function index(): View
    {
        return view('admin.service-categories.index', [
            'categories' => ServiceCategory::query()
                ->withCount('services')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.service-categories.create');
    }

    public function store(StoreServiceCategoryRequest $request): RedirectResponse
    {
        ServiceCategory::query()->create($request->validated());

        return redirect()->route('admin.service-categories.index')->with('status', 'Service category created.');
    }

    public function show(ServiceCategory $service_category): View
    {
        return view('admin.service-categories.show', [
            'category' => $service_category->load(['services' => fn ($query) => $query->orderBy('sort_order')]),
        ]);
    }

    public function edit(ServiceCategory $service_category): View
    {
        return view('admin.service-categories.edit', [
            'category' => $service_category,
        ]);
    }

    public function update(UpdateServiceCategoryRequest $request, ServiceCategory $service_category): RedirectResponse
    {
        $service_category->update($request->validated());

        $this->auditLogService->log(
            action: AuditAction::ServiceCategoryChanged->value,
            actor: $request->user(),
            auditableType: ServiceCategory::class,
            auditableId: $service_category->id,
            meta: ['service_category_public_id' => $service_category->public_id],
            request: $request,
        );

        return redirect()->route('admin.service-categories.index')->with('status', 'Service category updated.');
    }

    public function destroy(ServiceCategory $service_category): RedirectResponse
    {
        $service_category->delete();

        return redirect()->route('admin.service-categories.index')->with('status', 'Service category deleted.');
    }
}
