<x-app-layout>
<h1>{{ $category->name }}</h1>
<p>Slug: {{ $category->slug }}</p>
<p>Icon Name: {{ $category->icon_name }}</p>
<p>Image URL:
    @if($category->image_url)
        <a href="{{ $category->image_url }}" target="_blank" rel="noreferrer">{{ $category->image_url }}</a>
    @else
        -
    @endif
</p>
<p>Featured on home: {{ $category->is_featured ? 'Yes' : 'No' }}</p>
<p>Status: {{ $category->is_active ? 'Active' : 'Inactive' }}</p>
<div class="actions">
    <a class="btn" href="{{ route('admin.service-categories.edit', $category) }}">Edit</a>
    <form class="inline" method="POST" action="{{ route('admin.service-categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-light">Delete</button>
    </form>
</div>
<h3>Services</h3>
<table>
    <thead><tr><th>Name</th><th>Featured</th><th>Status</th></tr></thead>
    <tbody>
    @foreach($category->services as $service)
        <tr>
            <td>{{ $service->name }}</td>
            <td>{{ $service->is_featured ? 'Yes' : 'No' }}</td>
            <td>{{ $service->is_active ? 'Active' : 'Inactive' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</x-app-layout>
