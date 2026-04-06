<x-app-layout>
<div class="actions">
    <h1>{{ $zone->name }}</h1>
    <a class="btn" href="{{ route('admin.transport-zones.edit', $zone) }}">Edit</a>
</div>
<p>Slug: {{ $zone->slug }}</p>
<p>Fee: {{ number_format($zone->fee_amount) }} UGX</p>
<p>Radius: {{ $zone->radius_km !== null ? number_format((float) $zone->radius_km, 2).' km' : '—' }}</p>
<p>Coordinates:
    @if($zone->center_lat !== null && $zone->center_lng !== null)
        {{ $zone->center_lat }}, {{ $zone->center_lng }}
    @else
        —
    @endif
</p>
<p>Fallback: {{ $zone->is_fallback ? 'Yes' : 'No' }}</p>
<p>Status: {{ $zone->is_active ? 'Active' : 'Inactive' }}</p>
</x-app-layout>
