<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCommissionRuleRequest;
use App\Http\Requests\Admin\UpdateCommissionRuleRequest;
use App\Models\CommissionRule;
use App\Models\Service;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommissionRuleController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function index(): View
    {
        return view('admin.commission-rules.index', [
            'rules' => CommissionRule::query()->with('service')->latest()->paginate(20),
            'services' => Service::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.commission-rules.create', [
            'services' => Service::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreCommissionRuleRequest $request): RedirectResponse
    {
        $rule = CommissionRule::query()->create($request->validated());

        $this->auditLogService->log(
            action: AuditAction::CommissionRuleChanged->value,
            actor: $request->user(),
            auditableType: CommissionRule::class,
            auditableId: $rule->id,
            meta: ['operation' => 'create'],
            request: $request,
        );

        return redirect()->route('admin.commission-rules.index')->with('status', 'Commission rule created.');
    }

    public function show(CommissionRule $commissionRule): View
    {
        return view('admin.commission-rules.show', [
            'rule' => $commissionRule->load('service'),
        ]);
    }

    public function edit(CommissionRule $commissionRule): View
    {
        return view('admin.commission-rules.edit', [
            'rule' => $commissionRule,
            'services' => Service::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateCommissionRuleRequest $request, CommissionRule $commissionRule): RedirectResponse
    {
        $commissionRule->update($request->validated());

        $this->auditLogService->log(
            action: AuditAction::CommissionRuleChanged->value,
            actor: $request->user(),
            auditableType: CommissionRule::class,
            auditableId: $commissionRule->id,
            meta: ['operation' => 'update'],
            request: $request,
        );

        return redirect()->route('admin.commission-rules.index')->with('status', 'Commission rule updated.');
    }

    public function destroy(Request $request, CommissionRule $commissionRule): RedirectResponse
    {
        $commissionRule->delete();

        $this->auditLogService->log(
            action: AuditAction::CommissionRuleChanged->value,
            actor: $request->user(),
            auditableType: CommissionRule::class,
            auditableId: $commissionRule->id,
            meta: ['operation' => 'delete'],
            request: $request,
        );

        return redirect()->route('admin.commission-rules.index')->with('status', 'Commission rule deleted.');
    }
}
