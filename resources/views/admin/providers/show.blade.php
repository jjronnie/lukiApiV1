@extends('layouts.admin')

@section('content')
<h1>Provider {{ $provider->display_name }}</h1>

<div style="display:grid; gap:16px; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); margin-bottom: 24px;">
    <section style="border:1px solid rgba(148,163,184,.3); border-radius:16px; padding:18px;">
        <h3 style="margin-top:0;">Account</h3>
        <p><strong>User:</strong> {{ $provider->user->name }} ({{ $provider->user->email }})</p>
        <p><strong>Phone:</strong> {{ $provider->phone ?: ($provider->user->phone ?: '—') }}</p>
        <p><strong>Provider Number:</strong> {{ $provider->provider_number ?? 'Pending assignment' }}</p>
        <p><strong>Type:</strong> {{ ucfirst($provider->provider_type) }}</p>
        <p><strong>Onboarding:</strong> {{ $provider->onboarding_completed_at ? 'Completed' : 'Incomplete' }}</p>
        <p><strong>Address:</strong> {{ $provider->address_text ?? '—' }}</p>
        @if($provider->provider_type === 'business')
            <p><strong>Business Name:</strong> {{ $provider->business_name ?? '—' }}</p>
            <p><strong>Business Address:</strong> {{ $provider->business_address ?? '—' }}</p>
        @endif
        @if($provider->avatar_url)
            <p style="margin-top:16px;">
                <img src="{{ $provider->avatar_url }}" alt="{{ $provider->display_name }}" style="width: 96px; height: 96px; border-radius: 50%; object-fit: cover;">
            </p>
        @endif
    </section>

    <section style="border:1px solid rgba(148,163,184,.3); border-radius:16px; padding:18px;">
        <h3 style="margin-top:0;">Operational Status</h3>
        <p><strong>Verification:</strong> {{ $provider->verification_status->value ?? $provider->verification_status }}</p>
        <p><strong>Availability:</strong> {{ $provider->availability?->is_online ? 'Online' : 'Offline' }}</p>
        <p><strong>Last Seen:</strong> {{ $provider->availability?->last_seen_at ?? '—' }}</p>
        <p><strong>Wallet Balance:</strong> {{ number_format($provider->wallet?->balance_amount ?? 0) }}</p>
        <p><strong>Rating:</strong> {{ number_format((float) ($provider->rating_avg ?? 0), 2) }} / 5</p>
        <p><strong>Completed Jobs:</strong> {{ $provider->completed_orders_count }}</p>
        <p><strong>Cancelled Jobs:</strong> {{ $provider->cancelled_orders_count }}</p>
    </section>
</div>

<section style="border:1px solid rgba(148,163,184,.3); border-radius:16px; padding:18px; margin-bottom:24px;">
    <h3 style="margin-top:0;">Verification Action</h3>
    <form method="POST" action="{{ route('admin.providers.verification.update', $provider) }}" style="display:grid; gap:12px; max-width:540px;">
        @csrf
        <label>
            <span>Status</span>
            <select name="status" required>
                <option value="approved">Approve</option>
                <option value="rejected">Reject</option>
                <option value="suspended">Suspend</option>
            </select>
        </label>
        <label>
            <span>Reason</span>
            <textarea name="reason" rows="3" placeholder="Required for rejected service enrollments and helpful for review history."></textarea>
        </label>
        <button type="submit">Save Verification Review</button>
    </form>
    @if($provider->rejection_reason)
        <p style="margin-top:14px; color:#b91c1c;"><strong>Latest rejection reason:</strong> {{ $provider->rejection_reason }}</p>
    @endif
</section>

<section style="border:1px solid rgba(148,163,184,.3); border-radius:16px; padding:18px; margin-bottom:24px;">
    <h3 style="margin-top:0;">Service Enrollment Review</h3>
    <form method="POST" action="{{ route('admin.providers.services.update', $provider) }}">
        @csrf
        @php($assignedServices = $provider->providerServices->keyBy('service_id'))
        <div style="display:grid; gap:16px;">
            @foreach($services as $service)
                @php($assigned = $assignedServices->get($service->id))
                @php($assignedTierIds = $assigned?->eligibleTiers?->pluck('id')->all() ?? [])
                @php($approvalStatus = old("service_statuses.$service->id", $assigned?->approval_status?->value ?? $assigned?->approval_status ?? 'pending'))
                @php($reviewReason = old("service_review_reasons.$service->id", $assigned?->review_reason))
                <div style="border: 1px solid rgba(148, 163, 184, 0.35); border-radius: 14px; padding: 16px;">
                    <label style="display:flex; align-items:center; gap:12px;">
                        <input type="checkbox" name="service_ids[]" value="{{ $service->id }}" {{ $assigned?->is_active ? 'checked' : '' }} style="width:auto;">
                        <span>
                            <strong>{{ $service->name }}</strong>
                            <span style="display:block; font-size: 12px; opacity: .7;">
                                {{ $service->category?->name ?? 'No category' }} · From {{ number_format($service->base_price_amount) }} {{ $service->currency }}
                            </span>
                        </span>
                    </label>

                    <div style="display:grid; gap:12px; margin-top:14px; margin-left:28px;">
                        <label>
                            <span>Review status</span>
                            <select name="service_statuses[{{ $service->id }}]">
                                <option value="pending" {{ $approvalStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $approvalStatus === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="declined" {{ $approvalStatus === 'declined' ? 'selected' : '' }}>Declined</option>
                            </select>
                        </label>

                        <label>
                            <span>Review reason</span>
                            <textarea name="service_review_reasons[{{ $service->id }}]" rows="2" placeholder="Required when declined.">{{ $reviewReason }}</textarea>
                        </label>

                        @if($service->tiers->isNotEmpty())
                            <div style="display:grid; gap:10px;">
                                @foreach($service->tiers as $tier)
                                    <label style="display:flex; align-items:flex-start; gap:10px;">
                                        <input type="checkbox" name="tiers_by_service[{{ $service->id }}][]" value="{{ $tier->id }}" {{ in_array($tier->id, $assignedTierIds, true) ? 'checked' : '' }} style="width:auto;">
                                        <span>
                                            <strong>{{ $tier->name }}</strong> · {{ number_format($tier->price_amount) }} {{ $service->currency }}
                                            @if($tier->description)
                                                <span style="display:block; font-size: 12px; opacity: .7;">{{ $tier->description }}</span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div style="margin-top: 16px;">
            <button type="submit">Save Service Reviews</button>
        </div>
    </form>
</section>

<section style="border:1px solid rgba(148,163,184,.3); border-radius:16px; padding:18px; margin-bottom:24px;">
    <h3 style="margin-top:0;">Current Service Summary</h3>
    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th>Status</th>
                <th>Eligible Tiers</th>
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
            <tr><td colspan="4">No service enrollment requested yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</section>

<section style="border:1px solid rgba(148,163,184,.3); border-radius:16px; padding:18px;">
    <h3 style="margin-top:0;">Documents</h3>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Status</th>
                <th>Reviewed At</th>
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
                        <a class="btn btn-light" href="{{ route('admin.provider-documents.media', $document) }}" target="_blank">Open File</a>
                    @else
                        —
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="4">No legacy provider documents uploaded.</td></tr>
        @endforelse
        </tbody>
    </table>
</section>
@endsection
