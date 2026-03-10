@extends('layouts.admin')

@section('content')
<div class="actions">
    <h1>Service Categories</h1>
    <a class="btn" href="{{ route('admin.service-categories.create') }}">New Category</a>
</div>
<table>
    <thead><tr><th>Name</th><th>Slug</th><th>Icon</th><th>Services</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @foreach($categories as $category)
        <tr>
            <td>{{ $category->name }}</td>
            <td>{{ $category->slug }}</td>
            <td>{{ $category->icon_name }}</td>
            <td>{{ $category->services_count }}</td>
            <td>{{ $category->is_active ? 'Active' : 'Inactive' }}</td>
            <td class="actions">
                <a class="btn" href="{{ route('admin.service-categories.show', $category) }}">View</a>
                <a class="btn btn-light" href="{{ route('admin.service-categories.edit', $category) }}">Edit</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $categories->links() }}
@endsection
