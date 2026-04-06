<x-app-layout>
<div class="actions"><h1>Service Add-ons</h1><a class="btn" href="{{ route('admin.addons.create') }}">New Add-on</a></div>
<table>
    <thead><tr><th>Service</th><th>Name</th><th>Price</th><th></th></tr></thead>
    <tbody>
    @foreach($addons as $addon)
        <tr>
            <td>{{ $addon->service->name }}</td>
            <td>{{ $addon->name }}</td>
            <td>{{ number_format($addon->price_amount) }}</td>
            <td><a class="btn btn-light" href="{{ route('admin.addons.edit', $addon) }}">Edit</a></td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $addons->links() }}
</x-app-layout>
