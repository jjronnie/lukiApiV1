@extends('layouts.admin')

@section('content')
<h1>Order {{ $order->public_id }}</h1>
<p>Status: {{ $order->status->value ?? $order->status }}</p>
<p>Booking Mode: {{ $order->booking_mode->value ?? $order->booking_mode }}</p>
<p>Pair Provider Number: {{ $order->pair_provider_number ?? '—' }}</p>
<p>User: {{ $order->user->email }}</p>
<p>Provider: {{ $order->providerProfile?->display_name ?? '-' }}</p>
<p>Service: {{ $order->service_name_snapshot ?? $order->service?->name ?? '-' }}</p>
<p>Tier: {{ $order->service_tier_name_snapshot ?? $order->serviceTier?->name ?? 'Legacy order' }}</p>
<p>Total: {{ number_format($order->total_amount) }}</p>

<h3>Items</h3>
<table><thead><tr><th>Type</th><th>Name</th><th>Tier</th><th>Amount</th></tr></thead><tbody>
@foreach($order->items as $item)
<tr><td>{{ $item->item_type }}</td><td>{{ $item->name_snapshot }}</td><td>{{ $item->tier_name_snapshot ?? '—' }}</td><td>{{ number_format($item->line_total_amount) }}</td></tr>
@endforeach
</tbody></table>

<h3>Status History</h3>
<table><thead><tr><th>From</th><th>To</th><th>At</th></tr></thead><tbody>
@foreach($order->statusHistories as $history)
<tr><td>{{ $history->from_status }}</td><td>{{ $history->to_status }}</td><td>{{ $history->created_at }}</td></tr>
@endforeach
</tbody></table>
@endsection
