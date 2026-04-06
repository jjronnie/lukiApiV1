<x-app-layout>
<div class="actions">
    <h1>Transport Zones</h1>
    <a class="btn" href="{{ route('admin.transport-zones.create') }}">New Zone</a>
</div>
<table>
    <thead><tr><th>Name</th><th>Radius</th><th>Fee</th><th>Coordinates</th><th>Fallback</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @foreach($zones as $zone)
        <tr>
            <td>{{ $zone->name }}</td>
            <td>{{ $zone->radius_km !== null ? number_format((float) $zone->radius_km, 2).' km' : '—' }}</td>
            <td>{{ number_format($zone->fee_amount) }} UGX</td>
            <td>
                @if($zone->center_lat !== null && $zone->center_lng !== null)
                    {{ $zone->center_lat }}, {{ $zone->center_lng }}
                @else
                    —
                @endif
            </td>
            <td>{{ $zone->is_fallback ? 'Yes' : 'No' }}</td>
            <td>{{ $zone->is_active ? 'Active' : 'Inactive' }}</td>
            <td class="actions">
                <a class="btn" href="{{ route('admin.transport-zones.show', $zone) }}">View</a>
                <a class="btn btn-light" href="{{ route('admin.transport-zones.edit', $zone) }}">Edit</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $zones->links() }}
</x-app-layout>
