@extends('layouts.admin')

@section('content')
<h1>Orders</h1>
<table><thead><tr><th>Public ID</th><th>User</th><th>Provider</th><th>Status</th><th>Total</th><th></th></tr></thead><tbody>
@foreach($orders as $order)
<tr>
    <td>{{ $order->public_id }}</td>
    <td>{{ $order->user->email }}</td>
    <td>{{ $order->providerProfile?->display_name ?? '-' }}</td>
    <td>{{ $order->status->value ?? $order->status }}</td>
    <td>{{ number_format($order->total_amount) }}</td>
    <td><a class="btn" href="{{ route('admin.orders.show', $order) }}">View</a></td>
</tr>
@endforeach
</tbody></table>
{{ $orders->links() }}
@endsection
