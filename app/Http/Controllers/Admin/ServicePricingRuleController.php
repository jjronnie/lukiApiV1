<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServicePricingRuleRequest;
use App\Http\Requests\Admin\UpdateServicePricingRuleRequest;
use App\Models\Service;
use App\Models\ServicePricingRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServicePricingRuleController extends Controller
{
    public function index(): View
    {
        return view('admin.pricing-rules.index', [
            'rules' => ServicePricingRule::query()->with('service')->orderBy('priority')->paginate(20),
            'services' => Service::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pricing-rules.create', [
            'services' => Service::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreServicePricingRuleRequest $request): RedirectResponse
    {
        ServicePricingRule::query()->create($request->validated());

        return redirect()->route('admin.pricing-rules.index')->with('status', 'Pricing rule created.');
    }

    public function show(ServicePricingRule $servicePricingRule): View
    {
        return view('admin.pricing-rules.show', [
            'rule' => $servicePricingRule->load('service'),
        ]);
    }

    public function edit(ServicePricingRule $servicePricingRule): View
    {
        return view('admin.pricing-rules.edit', [
            'rule' => $servicePricingRule,
            'services' => Service::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateServicePricingRuleRequest $request, ServicePricingRule $servicePricingRule): RedirectResponse
    {
        $servicePricingRule->update($request->validated());

        return redirect()->route('admin.pricing-rules.index')->with('status', 'Pricing rule updated.');
    }

    public function destroy(ServicePricingRule $servicePricingRule): RedirectResponse
    {
        $servicePricingRule->delete();

        return redirect()->route('admin.pricing-rules.index')->with('status', 'Pricing rule deleted.');
    }
}
