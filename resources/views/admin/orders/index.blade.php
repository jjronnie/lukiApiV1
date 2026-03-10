@extends('layouts.admin')

@section('content')
<h1>Orders</h1>
<table><thead><tr><th>Public ID</th><th>Service</th><th>Tier</th><th>Mode</th><th>User</th><th>Provider</th><th>Status</th><th>Total</th><th></th></tr></thead><tbody>
@foreach($orders as $order)
<tr>
    <td>{{ $order->public_id }}</td>
    <td>{{ $order->service_name_snapshot ?? $order->service?->name ?? '-' }}</td>
    <td>{{ $order->service_tier_name_snapshot ?? $order->serviceTier?->name ?? 'Legacy' }}</td>
    <td>{{ $order->booking_mode->value ?? $order->booking_mode }}</td>
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
