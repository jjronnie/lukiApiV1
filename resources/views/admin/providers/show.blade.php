@extends('layouts.admin')

@section('content')
<h1>Provider {{ $provider->display_name }}</h1>
<p>User: {{ $provider->user->name }} ({{ $provider->user->email }})</p>
<p>Provider Number: <strong>{{ $provider->provider_number ?? 'Pending assignment' }}</strong></p>
<p>Status: {{ $provider->verification_status->value ?? $provider->verification_status }}</p>
<p>Availability: {{ $provider->availability?->is_online ? 'Online' : 'Offline' }}</p>
<p>Wallet Balance: {{ number_format($provider->wallet?->balance_amount ?? 0) }}</p>
@if($provider->avatar_url)
    <p><img src="{{ $provider->avatar_url }}" alt="{{ $provider->display_name }}" style="width: 96px; height: 96px; border-radius: 50%; object-fit: cover;"></p>
@endif

<h3>Verification Action</h3>
<form method="POST" action="{{ route('admin.providers.verification.update', $provider) }}">
    @csrf
    <label>Status</label>
    <select name="status" required>
        <option value="approved">Approve</option>
        <option value="rejected">Reject</option>
        <option value="suspended">Suspend</option>
    </select>
    <label>Reason</label>
    <textarea name="reason"></textarea>
    <button type="submit">Submit</button>
</form>

<h3>Service Eligibility</h3>
<form method="POST" action="{{ route('admin.providers.services.update', $provider) }}">
    @csrf
    @php($assignedServices = $provider->providerServices->keyBy('service_id'))
    <div style="display:grid; gap:16px;">
        @foreach($services as $service)
            @php($assigned = $assignedServices->get($service->id))
            @php($assignedTierIds = $assigned?->eligibleTiers?->pluck('id')->all() ?? [])
            <div style="border: 1px solid rgba(148, 163, 184, 0.35); border-radius: 14px; padding: 16px;">
                <label style="display:flex; align-items:center; gap:12px;">
                    <input type="checkbox" name="service_ids[]" value="{{ $service->id }}" {{ $assigned ? 'checked' : '' }} style="width:auto;">
                    <span>
                        <strong>{{ $service->name }}</strong>
                        <span style="display:block; font-size: 12px; opacity: .7;">
                            {{ $service->category?->name ?? 'No category' }} · From {{ number_format($service->base_price_amount) }} {{ $service->currency }}
                        </span>
                    </span>
                </label>

                @if($service->tiers->isNotEmpty())
                    <div style="margin-top: 12px; display:grid; gap:10px;">
                        @foreach($service->tiers as $tier)
                            <label style="display:flex; align-items:flex-start; gap:10px; margin-left: 28px;">
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
        @endforeach
    </div>
    <div style="margin-top: 16px;">
        <button type="submit">Save Service Eligibility</button>
    </div>
</form>

<h3>Current Provider Offering Summary</h3>
<table><thead><tr><th>Service</th><th>Eligible Tiers</th></tr></thead><tbody>
@forelse($provider->providerServices as $providerService)
<tr>
    <td>{{ $providerService->service?->name ?? 'Unknown service' }}</td>
    <td>{{ $providerService->eligibleTiers->pluck('name')->join(', ') ?: 'No tiers assigned' }}</td>
</tr>
@empty
<tr><td colspan="2">No service eligibility assigned yet.</td></tr>
@endforelse
</tbody></table>

<h3>Documents</h3>
<table><thead><tr><th>Type</th><th>Status</th><th>Reviewed At</th><th>File</th></tr></thead><tbody>
@foreach($provider->documents as $document)
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
@endforeach
</tbody></table>
@endsection
