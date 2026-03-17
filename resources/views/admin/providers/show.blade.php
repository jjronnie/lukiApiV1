@extends('layouts.admin')

@php
    $identityVerification = $provider->user?->providerIdentityVerification;
    $currentVerificationStatus = $provider->verification_status->value ?? $provider->verification_status;
    $selectedVerificationStatus = old('status', in_array($currentVerificationStatus, ['approved', 'rejected', 'suspended'], true) ? $currentVerificationStatus : 'approved');
    $assignedServices = $provider->providerServices->keyBy('service_id');
    $initialReviewedServiceId = (int) old('selected_service_id', $provider->providerServices->sortByDesc('requested_at')->first()?->service_id ?? $services->first()?->id);
@endphp

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#8b7d72]">Provider profile</p>
                <h1 class="mt-1 text-2xl font-display">{{ $provider->display_name }}</h1>
            </div>
            <a href="{{ route('admin.providers.index') }}" class="btn btn-light">Back to providers</a>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <section class="admin-card space-y-4 lg:col-span-1">
                <div class="flex items-start gap-4">
                    @if($provider->avatar_url)
                        <img src="{{ $provider->avatar_url }}" alt="{{ $provider->display_name }}" class="h-20 w-20 rounded-full object-cover">
                    @else
                        <div class="grid h-20 w-20 place-items-center rounded-full bg-[#fff2e2] text-[#d76a08]">N/A</div>
                    @endif
                    <div>
                        <p class="admin-card-label">Account</p>
                        <h2 class="mt-2 text-xl font-semibold text-[#1f2937]">{{ $provider->user->name }}</h2>
                        <p class="mt-1 text-sm text-[#6b7280]">{{ $provider->user->email }}</p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                    <div class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                        <p class="admin-card-label">Provider number</p>
                        <p class="mt-2 text-base font-semibold text-[#1f2937]">{{ $provider->provider_number ?? 'Pending assignment' }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                        <p class="admin-card-label">Operating type</p>
                        <p class="mt-2 text-base font-semibold text-[#1f2937]">{{ ucfirst($provider->provider_type) }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                        <p class="admin-card-label">Phone</p>
                        <p class="mt-2 text-base font-semibold text-[#1f2937]">{{ $provider->phone ?: ($provider->user->phone ?: '—') }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                        <p class="admin-card-label">Onboarding</p>
                        <p class="mt-2 text-base font-semibold text-[#1f2937]">{{ $provider->onboarding_completed_at ? 'Completed' : 'Incomplete' }}</p>
                    </div>
                </div>
            </section>

            <section class="admin-card space-y-4 lg:col-span-1">
                <div>
                    <p class="admin-card-label">Operational status</p>
                    <h2 class="mt-2 text-xl font-semibold text-[#1f2937]">Provider operations</h2>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                        <p class="admin-card-label">Verification</p>
                        <p class="mt-2 text-base font-semibold capitalize text-[#1f2937]">{{ str_replace('_', ' ', $currentVerificationStatus) }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                        <p class="admin-card-label">Availability</p>
                        <p class="mt-2 text-base font-semibold text-[#1f2937]">{{ $provider->availability?->is_online ? 'Online' : 'Offline' }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                        <p class="admin-card-label">Wallet balance</p>
                        <p class="mt-2 text-base font-semibold text-[#1f2937]">{{ number_format($provider->wallet?->balance_amount ?? 0) }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                        <p class="admin-card-label">Last seen</p>
                        <p class="mt-2 text-base font-semibold text-[#1f2937]">{{ $provider->availability?->last_seen_at ?? '—' }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                        <p class="admin-card-label">Completed jobs</p>
                        <p class="mt-2 text-base font-semibold text-[#1f2937]">{{ $provider->completed_orders_count }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                        <p class="admin-card-label">Cancelled jobs</p>
                        <p class="mt-2 text-base font-semibold text-[#1f2937]">{{ $provider->cancelled_orders_count }}</p>
                    </div>
                </div>
            </section>

            <section class="admin-card space-y-4 lg:col-span-1">
                <div>
                    <p class="admin-card-label">Profile details</p>
                    <h2 class="mt-2 text-xl font-semibold text-[#1f2937]">Address and business</h2>
                </div>

                <div class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                    <p class="admin-card-label">Address</p>
                    <p class="mt-2 text-sm text-[#1f2937]">{{ $provider->address_text ?? '—' }}</p>
                </div>

                @if($provider->provider_type === 'business')
                    <div class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                        <p class="admin-card-label">Business name</p>
                        <p class="mt-2 text-sm text-[#1f2937]">{{ $provider->business_name ?? '—' }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                        <p class="admin-card-label">Business address</p>
                        <p class="mt-2 text-sm text-[#1f2937]">{{ $provider->business_address ?? '—' }}</p>
                    </div>
                @endif
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
            <section class="admin-card space-y-5">
                <div class="flex flex-col gap-2">
                    <p class="admin-card-label">Provider verification</p>
                    <h2 class="text-xl font-semibold text-[#1f2937]">Review verification details</h2>
                    @if(!$identityVerification)
                        <p class="text-sm text-[#6b7280]">No provider verification submission has been received yet.</p>
                    @endif
                </div>

                <form
                    method="POST"
                    action="{{ route('admin.providers.verification.update', $provider) }}"
                    class="space-y-4"
                    data-ug-locale-form
                    data-initial-district="{{ old('district_id', $identityVerification?->district_id) }}"
                    data-initial-county="{{ old('county_id', $identityVerification?->county_id) }}"
                    data-initial-sub-county="{{ old('sub_county_id', $identityVerification?->sub_county_id) }}"
                    data-initial-parish="{{ old('parish_id', $identityVerification?->parish_id) }}"
                    data-initial-village="{{ old('village_id', $identityVerification?->village_id) }}"
                >
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="approved" {{ $selectedVerificationStatus === 'approved' ? 'selected' : '' }}>Approve</option>
                                <option value="rejected" {{ $selectedVerificationStatus === 'rejected' ? 'selected' : '' }}>Reject</option>
                                <option value="suspended" {{ $selectedVerificationStatus === 'suspended' ? 'selected' : '' }}>Suspend</option>
                            </select>
                        </div>
                        <div>
                            <label for="id_number">ID number</label>
                            <input id="id_number" type="text" name="id_number" value="{{ old('id_number', $identityVerification?->id_number) }}" placeholder="Enter ID number">
                        </div>
                        <div>
                            <label for="date_of_birth">Date of birth</label>
                            <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($identityVerification?->date_of_birth)->format('Y-m-d')) }}">
                        </div>
                        <div>
                            <label for="district_id">District</label>
                            <select id="district_id" name="district_id" data-ug-district></select>
                            <input type="hidden" name="district_name" value="{{ old('district_name', $identityVerification?->district_name) }}">
                        </div>
                        <div>
                            <label for="county_id">County</label>
                            <select id="county_id" name="county_id" data-ug-county></select>
                            <input type="hidden" name="county_name" value="{{ old('county_name', $identityVerification?->county_name) }}">
                        </div>
                        <div>
                            <label for="sub_county_id">Sub-county</label>
                            <select id="sub_county_id" name="sub_county_id" data-ug-sub-county></select>
                            <input type="hidden" name="sub_county_name" value="{{ old('sub_county_name', $identityVerification?->sub_county_name) }}">
                        </div>
                        <div>
                            <label for="parish_id">Parish</label>
                            <select id="parish_id" name="parish_id" data-ug-parish></select>
                            <input type="hidden" name="parish_name" value="{{ old('parish_name', $identityVerification?->parish_name) }}">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="village_id">Village</label>
                            <select id="village_id" name="village_id" data-ug-village></select>
                            <input type="hidden" name="village_name" value="{{ old('village_name', $identityVerification?->village_name) }}">
                        </div>
                    </div>

                    <div>
                        <label for="reason">Review reason</label>
                        <textarea id="reason" name="reason" rows="4" placeholder="Required when rejecting or suspending.">{{ old('reason', $provider->rejection_reason ?? $identityVerification?->rejection_reason) }}</textarea>
                    </div>

                    <button type="submit" class="btn w-full">Save verification review</button>
                </form>

                @if($identityVerification)
                    <div class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                        <p class="admin-card-label">Current admin identity details</p>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.1em] text-[#8b7d72]">ID Number</p>
                                <p class="mt-1 text-sm text-[#1f2937]">{{ $identityVerification->id_number ?: '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.1em] text-[#8b7d72]">Date of Birth</p>
                                <p class="mt-1 text-sm text-[#1f2937]">{{ $identityVerification->date_of_birth?->format('d M Y') ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.1em] text-[#8b7d72]">District</p>
                                <p class="mt-1 text-sm text-[#1f2937]">{{ $identityVerification->district_name ?: '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.1em] text-[#8b7d72]">County</p>
                                <p class="mt-1 text-sm text-[#1f2937]">{{ $identityVerification->county_name ?: '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.1em] text-[#8b7d72]">Sub-county</p>
                                <p class="mt-1 text-sm text-[#1f2937]">{{ $identityVerification->sub_county_name ?: '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.1em] text-[#8b7d72]">Parish</p>
                                <p class="mt-1 text-sm text-[#1f2937]">{{ $identityVerification->parish_name ?: '—' }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <p class="text-xs font-semibold uppercase tracking-[0.1em] text-[#8b7d72]">Village</p>
                                <p class="mt-1 text-sm text-[#1f2937]">{{ $identityVerification->village_name ?: '—' }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </section>

            <section class="admin-card space-y-5">
                <div class="flex flex-col gap-2">
                    <p class="admin-card-label">Verification media</p>
                    <h2 class="text-xl font-semibold text-[#1f2937]">Submitted images</h2>
                    @if($identityVerification && $identityVerification->canDeleteIdImages())
                        <p class="text-sm text-[#6b7280]">Front and back images can now be deleted if they are no longer needed.</p>
                    @endif
                </div>

                @if($identityVerification)
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach (['selfie' => 'Selfie', 'id_front' => 'ID Front', 'id_back' => 'ID Back', 'business_license' => 'Business licence'] as $collection => $label)
                            @php($media = $identityVerification->getFirstMedia($collection))
                            <article class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <h3 class="text-sm font-semibold text-[#1f2937]">{{ $label }}</h3>
                                    @if($media)
                                        <a
                                            href="{{ route('admin.providers.verification.media', [$provider, $collection]) }}"
                                            target="_blank"
                                            class="text-xs font-semibold uppercase tracking-[0.08em] text-[#d76a08]"
                                        >
                                            Open
                                        </a>
                                    @endif
                                </div>

                                @if($media)
                                    <a href="{{ route('admin.providers.verification.media', [$provider, $collection]) }}" target="_blank">
                                        <img
                                            src="{{ route('admin.providers.verification.media', [$provider, $collection]) }}"
                                            alt="{{ $label }}"
                                            class="h-64 w-full rounded-2xl bg-[#111827] object-contain p-3"
                                        >
                                    </a>
                                @else
                                    <div class="grid h-64 place-items-center rounded-2xl border border-dashed border-[#d8cec2] bg-white text-sm text-[#8b7d72]">
                                        Image not available
                                    </div>
                                @endif

                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if($media)
                                        <a class="btn btn-light" href="{{ route('admin.providers.verification.media', [$provider, $collection]) }}" target="_blank">Open full image</a>
                                    @endif
                                    @if($media && in_array($collection, ['id_front', 'id_back'], true) && $identityVerification->canDeleteIdImages())
                                        <form method="POST" action="{{ route('admin.providers.verification.media.destroy', [$provider, $collection]) }}" onsubmit="return confirm('Delete this image from the verification record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light">Delete image</button>
                                        </form>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="grid h-52 place-items-center rounded-2xl border border-dashed border-[#d8cec2] bg-[#fffdf9] text-center text-sm text-[#8b7d72]">
                        No provider verification images have been submitted yet.
                    </div>
                @endif
            </section>
        </div>

        <section class="admin-card space-y-5">
            <div>
                <p class="admin-card-label">Service enrollment review</p>
                <h2 class="mt-2 text-xl font-semibold text-[#1f2937]">Approve services and tiers</h2>
            </div>

            <form method="POST" action="{{ route('admin.providers.services.update', $provider) }}" class="space-y-5" data-service-review-form>
                @csrf
                <input type="hidden" name="service_action" value="{{ old('service_action') }}" data-selected-service-action>

                <div class="grid gap-5 xl:grid-cols-[280px_minmax(0,1fr)]">
                    <aside class="rounded-3xl border border-[#f1e7dd] bg-[#fffdf9] p-5">
                        <label for="selected_service_id">Select service</label>
                        <select id="selected_service_id" name="selected_service_id" data-service-selector>
                            @foreach($services as $service)
                                @php($assigned = $assignedServices->get($service->id))
                                @php($statusLabel = ucfirst($assigned?->approval_status?->value ?? $assigned?->approval_status ?? 'pending'))
                                <option
                                    value="{{ $service->id }}"
                                    data-service-name="{{ $service->name }}"
                                    {{ $initialReviewedServiceId === $service->id ? 'selected' : '' }}
                                >
                                    {{ $service->name }} · {{ $statusLabel }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-3 text-sm leading-6 text-[#6b7280]">
                            Choose one service to review at a time, then use the action buttons to approve, reject, or suspend it.
                        </p>

                        <div class="mt-5 grid gap-3">
                            <div class="rounded-2xl border border-[#f1e7dd] bg-white px-4 py-3">
                                <p class="admin-card-label">Requested services</p>
                                <p class="mt-2 text-lg font-semibold text-[#1f2937]">{{ $provider->providerServices->count() }}</p>
                            </div>
                            <div class="rounded-2xl border border-[#f1e7dd] bg-white px-4 py-3">
                                <p class="admin-card-label">Approved services</p>
                                <p class="mt-2 text-lg font-semibold text-[#1f2937]">{{ $provider->providerServices->filter(fn ($providerService) => ($providerService->approval_status?->value ?? $providerService->approval_status) === 'approved')->count() }}</p>
                            </div>
                        </div>
                    </aside>

                    <div class="space-y-4">
                        @foreach($services as $service)
                            @php($assigned = $assignedServices->get($service->id))
                            @php($assignedTierIds = $assigned?->eligibleTiers?->pluck('id')->all() ?? [])
                            @php($selectedTierIds = collect(old("service_configurations.$service->id.tier_ids", $assignedTierIds))->map(fn ($id) => (int) $id)->all())
                            @php($reviewReason = old("service_configurations.$service->id.review_reason", $assigned?->review_reason))
                            @php($serviceStatus = $assigned?->approval_status?->value ?? $assigned?->approval_status ?? 'pending')

                            <article
                                class="rounded-3xl border border-[#f1e7dd] bg-[#fffdf9] p-5"
                                data-service-panel="{{ $service->id }}"
                                @if($initialReviewedServiceId !== $service->id) hidden @endif
                            >
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <p class="text-lg font-semibold text-[#1f2937]">{{ $service->name }}</p>
                                        <p class="mt-1 text-sm text-[#6b7280]">
                                            {{ $service->category?->name ?? 'No category' }} · From {{ number_format($service->base_price_amount) }} {{ $service->currency }}
                                        </p>
                                    </div>
                                    <span class="inline-flex rounded-full bg-[#fff2e2] px-3 py-1 text-xs font-semibold uppercase tracking-[0.12em] text-[#d76a08]">
                                        {{ ucfirst($serviceStatus) }}
                                    </span>
                                </div>

                                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                                    <div class="rounded-2xl border border-[#f1e7dd] bg-white p-4">
                                        <p class="admin-card-label">Current status</p>
                                        <p class="mt-2 text-sm font-semibold text-[#1f2937]">{{ ucfirst($serviceStatus) }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-[#f1e7dd] bg-white p-4">
                                        <p class="admin-card-label">Requested</p>
                                        <p class="mt-2 text-sm font-semibold text-[#1f2937]">{{ $assigned?->requested_at?->format('d M Y · H:i') ?? 'Not requested yet' }}</p>
                                    </div>
                                </div>

                                <div class="mt-5">
                                    <label for="service_reason_{{ $service->id }}">Review reason</label>
                                    <textarea
                                        id="service_reason_{{ $service->id }}"
                                        name="service_configurations[{{ $service->id }}][review_reason]"
                                        rows="3"
                                        placeholder="Required when rejecting or suspending this service."
                                    >{{ $reviewReason }}</textarea>
                                </div>

                                @if($service->tiers->isNotEmpty())
                                    <div class="mt-5 space-y-3">
                                        <div>
                                            <p class="admin-card-label">Eligible tiers</p>
                                            <p class="mt-1 text-sm text-[#6b7280]">Choose the tiers this provider can handle for the selected service.</p>
                                        </div>
                                        <div class="grid gap-3 lg:grid-cols-2">
                                            @foreach($service->tiers as $tier)
                                                <label class="flex items-start gap-3 rounded-2xl border border-[#f1e7dd] bg-white p-4">
                                                    <input
                                                        type="checkbox"
                                                        name="service_configurations[{{ $service->id }}][tier_ids][]"
                                                        value="{{ $tier->id }}"
                                                        {{ in_array($tier->id, $selectedTierIds, true) ? 'checked' : '' }}
                                                        style="width:auto; margin-top:2px;"
                                                    >
                                                    <span>
                                                        <strong class="text-sm text-[#1f2937]">{{ $tier->name }}</strong>
                                                        <span class="mt-1 block text-xs text-[#6b7280]">{{ number_format($tier->price_amount) }} {{ $service->currency }}</span>
                                                        @if($tier->description)
                                                            <span class="mt-1 block text-xs text-[#8b7d72]">{{ $tier->description }}</span>
                                                        @endif
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </div>

                <div class="sticky bottom-5 z-20 flex justify-end gap-3 rounded-3xl border border-[#f1e7dd] bg-white/95 p-3 shadow-lg shadow-black/5 backdrop-blur">
                    <button type="button" class="rounded-2xl border border-[#d6d3d1] px-5 py-3 text-sm font-semibold text-[#57534e]" data-service-action="suspended">Suspend</button>
                    <button type="button" class="rounded-2xl border border-[#fca5a5] px-5 py-3 text-sm font-semibold text-[#b91c1c]" data-service-action="declined">Reject</button>
                    <button type="button" class="btn" data-service-action="approved">Approve</button>
                </div>
            </form>

            <x-admin.confirm-action-modal
                id="provider-service-action-modal"
                title="Confirm service action"
                heading="Confirm service review"
                description="Please confirm the action for the selected service."
                confirm-label="Yes, continue"
                cancel-label="Not now"
            />
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="admin-card">
                <div class="mb-4">
                    <p class="admin-card-label">Current service summary</p>
                    <h2 class="mt-2 text-xl font-semibold text-[#1f2937]">Assigned services</h2>
                </div>

                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Eligible tiers</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($provider->providerServices as $providerService)
                                <tr>
                                    <td>{{ $providerService->service?->name ?? 'Unknown service' }}</td>
                                    <td>{{ $providerService->approval_status->value ?? $providerService->approval_status }}</td>
                                    <td>{{ $providerService->eligibleTiers->pluck('name')->join(', ') ?: 'No tiers assigned' }}</td>
                                    <td>{{ $providerService->review_reason ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">No service enrollment requested yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="admin-card">
                <div class="mb-4">
                    <p class="admin-card-label">Legacy documents</p>
                    <h2 class="mt-2 text-xl font-semibold text-[#1f2937]">Provider document records</h2>
                </div>

                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Reviewed at</th>
                                <th>File</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($provider->documents as $document)
                                <tr>
                                    <td>{{ $document->document_type }}</td>
                                    <td>{{ $document->status }}</td>
                                    <td>{{ $document->reviewed_at }}</td>
                                    <td>
                                        @if($document->getFirstMedia('documents'))
                                            <a class="btn btn-light" href="{{ route('admin.provider-documents.media', $document) }}" target="_blank">Open file</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">No legacy provider documents uploaded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const serviceReviewForm = document.querySelector('[data-service-review-form]');
            if (!serviceReviewForm) {
                return;
            }

            const selector = serviceReviewForm.querySelector('[data-service-selector]');
            const panels = Array.from(serviceReviewForm.querySelectorAll('[data-service-panel]'));
            const actionInput = serviceReviewForm.querySelector('[data-selected-service-action]');
            const modal = document.getElementById('provider-service-action-modal');
            const modalHeading = modal?.querySelector('[data-confirm-modal-heading]');
            const modalDescription = modal?.querySelector('[data-confirm-modal-description]');
            const modalConfirm = modal?.querySelector('[data-confirm-modal-confirm]');
            const modalCancelTargets = modal?.querySelectorAll('[data-confirm-modal-cancel], [data-confirm-modal-backdrop]') ?? [];
            let pendingSubmit = null;

            const syncPanels = () => {
                const selectedServiceId = selector?.value;
                panels.forEach((panel) => {
                    panel.hidden = panel.dataset.servicePanel !== selectedServiceId;
                });
            };

            const closeModal = () => {
                if (!modal) {
                    return;
                }

                modal.classList.add('hidden');
                modal.classList.remove('flex');
                pendingSubmit = null;
            };

            const openModal = () => {
                if (!modal) {
                    return;
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };

            syncPanels();
            selector?.addEventListener('change', syncPanels);

            serviceReviewForm.querySelectorAll('[data-service-action]').forEach((button) => {
                button.addEventListener('click', () => {
                    if (!selector || !actionInput) {
                        return;
                    }

                    const selectedOption = selector.selectedOptions[0];
                    if (!selectedOption) {
                        return;
                    }

                    const action = button.dataset.serviceAction;
                    const serviceName = selectedOption.dataset.serviceName || 'this service';
                    const actionLabel = button.textContent.trim();

                    actionInput.value = action;
                    if (modalHeading) {
                        modalHeading.textContent = `${actionLabel} ${serviceName}`;
                    }
                    if (modalDescription) {
                        modalDescription.textContent = `Please confirm you want to ${actionLabel.toLowerCase()} ${serviceName}.`;
                    }

                    pendingSubmit = () => serviceReviewForm.submit();
                    openModal();
                });
            });

            modalCancelTargets.forEach((element) => {
                element.addEventListener('click', closeModal);
            });

            modalConfirm?.addEventListener('click', () => {
                if (pendingSubmit) {
                    pendingSubmit();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });
        });
    </script>
@endsection
