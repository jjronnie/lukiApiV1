<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceAddOnRequest;
use App\Http\Requests\Admin\UpdateServiceAddOnRequest;
use App\Models\Service;
use App\Models\ServiceAddOn;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceAddOnController extends Controller
{
    public function index(): View
    {
        return view('admin.addons.index', [
            'addons' => ServiceAddOn::query()->with('service')->orderBy('service_id')->orderBy('sort_order')->paginate(20),
            'services' => Service::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.addons.create', [
            'services' => Service::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreServiceAddOnRequest $request): RedirectResponse
    {
        ServiceAddOn::query()->create($request->validated());

        return redirect()->route('admin.addons.index')->with('status', 'Add-on created.');
    }

    public function show(ServiceAddOn $serviceAddOn): View
    {
        return view('admin.addons.show', [
            'addon' => $serviceAddOn->load('service'),
        ]);
    }

    public function edit(ServiceAddOn $serviceAddOn): View
    {
        return view('admin.addons.edit', [
            'addon' => $serviceAddOn,
            'services' => Service::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateServiceAddOnRequest $request, ServiceAddOn $serviceAddOn): RedirectResponse
    {
        $serviceAddOn->update($request->validated());

        return redirect()->route('admin.addons.index')->with('status', 'Add-on updated.');
    }

    public function destroy(ServiceAddOn $serviceAddOn): RedirectResponse
    {
        $serviceAddOn->delete();

        return redirect()->route('admin.addons.index')->with('status', 'Add-on deleted.');
    }
}
