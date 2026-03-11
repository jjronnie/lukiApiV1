@extends('layouts.admin')

@section('content')
<div class="actions">
    <h1>Home Adverts</h1>
    <a class="btn" href="{{ route('admin.home-adverts.create') }}">New Advert</a>
</div>
<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Button</th>
            <th>Link</th>
            <th>Status</th>
            <th>Window</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    @foreach($adverts as $advert)
        <tr>
            <td>{{ $advert->title }}</td>
            <td>{{ $advert->button_text ?: '-' }}</td>
            <td>{{ $advert->link_type }}{{ $advert->link_target ? ': ' . $advert->link_target : '' }}</td>
            <td>{{ $advert->is_active ? 'Active' : 'Inactive' }}</td>
            <td>
                {{ $advert->starts_at?->format('d M Y H:i') ?? 'Now' }}
                -
                {{ $advert->ends_at?->format('d M Y H:i') ?? 'Open' }}
            </td>
            <td class="actions">
                <a class="btn" href="{{ route('admin.home-adverts.show', $advert) }}">View</a>
                <a class="btn btn-light" href="{{ route('admin.home-adverts.edit', $advert) }}">Edit</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $adverts->links() }}
@endsection
