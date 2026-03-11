@extends('layouts.admin')

@section('content')
<h1>{{ $service->name }}</h1>
<p>Category: {{ $service->category?->name ?? '—' }}</p>
<p>Slug: {{ $service->slug }}</p>
<p>Icon Name: {{ $service->icon_name }}</p>
<p>Image URL:
    @if($service->image_url)
        <a href="{{ $service->image_url }}" target="_blank" rel="noreferrer">{{ $service->image_url }}</a>
    @else
        —
    @endif
</p>
<p>From Price: {{ number_format($service->base_price_amount) }} {{ $service->currency }}</p>
<p>Featured: {{ $service->is_featured ? 'Yes' : 'No' }}</p>
<p>Status: {{ $service->is_active ? 'Active' : 'Inactive' }}</p>
@if($service->image_url)
    <p><img src="{{ $service->image_url }}" alt="{{ $service->name }}" style="max-width: 280px; border-radius: 16px;"></p>
@endif
<div class="actions">
    <a class="btn" href="{{ route('admin.services.edit', $service) }}">Edit</a>
    <form class="inline" method="POST" action="{{ route('admin.services.destroy', $service) }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-light">Delete</button>
    </form>
</div>
<h3>Service Tiers</h3>
<table><thead><tr><th>Name</th><th>Price</th><th>Status</th><th>Sort</th></tr></thead><tbody>
@foreach($service->tiers as $tier)
<tr>
    <td>{{ $tier->name }}</td>
    <td>{{ number_format($tier->price_amount) }} {{ $service->currency }}</td>
    <td>{{ $tier->is_active ? 'Active' : 'Inactive' }}</td>
    <td>{{ $tier->sort_order }}</td>
</tr>
@endforeach
</tbody></table>
<h3>Add-ons</h3>
<table><thead><tr><th>Name</th><th>Price</th></tr></thead><tbody>
@foreach($service->addOns as $addOn)
<tr><td>{{ $addOn->name }}</td><td>{{ number_format($addOn->price_amount) }}</td></tr>
@endforeach
</tbody></table>
<h3>Pricing Rules</h3>
<table><thead><tr><th>Type</th><th>Priority</th></tr></thead><tbody>
@foreach($service->pricingRules as $rule)
<tr><td>{{ $rule->rule_type->value ?? $rule->rule_type }}</td><td>{{ $rule->priority }}</td></tr>
@endforeach
</tbody></table>
@endsection
