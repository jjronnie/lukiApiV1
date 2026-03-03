@extends('layouts.admin')

@section('content')
<h1>{{ $service->name }}</h1>
<p>Slug: {{ $service->slug }}</p>
<p>Price: {{ number_format($service->base_price_amount) }} {{ $service->currency }}</p>
<p>Status: {{ $service->is_active ? 'Active' : 'Inactive' }}</p>
<div class="actions">
    <a class="btn" href="{{ route('admin.services.edit', $service) }}">Edit</a>
    <form class="inline" method="POST" action="{{ route('admin.services.destroy', $service) }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-light">Delete</button>
    </form>
</div>
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
