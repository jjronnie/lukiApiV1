<x-app-layout>
<div class="actions">
    <h1>Services</h1>
    <a class="btn" href="{{ route('admin.services.create') }}">New Service</a>
</div>
<table>
    <thead><tr><th>Name</th><th>Category</th><th>Image</th><th>Icon</th><th>Slug</th><th>From Price</th><th>Active Tiers</th><th>Featured</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @foreach($services as $service)
        <tr>
            <td>{{ $service->name }}</td>
            <td>{{ $service->category?->name ?? '—' }}</td>
            <td>
                @if($service->image_url)
                    <a href="{{ $service->image_url }}" target="_blank" rel="noreferrer">Preview</a>
                @else
                    —
                @endif
            </td>
            <td>{{ $service->icon_name }}</td>
            <td>{{ $service->slug }}</td>
            <td>{{ number_format($service->base_price_amount) }} {{ $service->currency }}</td>
            <td>{{ $service->tiers_count }}</td>
            <td>{{ $service->is_featured ? 'Yes' : 'No' }}</td>
            <td>{{ $service->is_active ? 'Active' : 'Inactive' }}</td>
            <td class="actions">
                <a class="btn" href="{{ route('admin.services.show', $service) }}">View</a>
                <a class="btn btn-light" href="{{ route('admin.services.edit', $service) }}">Edit</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $services->links() }}
</x-app-layout>
