<x-app-layout>
<h1>Order {{ $order->public_id }}</h1>
<p>Status: {{ $order->status->value ?? $order->status }}</p>
<p>Booking Mode: {{ $order->booking_mode->value ?? $order->booking_mode }}</p>
<p>Pair Provider Number: {{ $order->pair_provider_number ?? '—' }}</p>
<p>User: {{ $order->user->email }}</p>
<p>Provider: {{ $order->providerProfile?->display_name ?? '-' }}</p>
<p>Service: {{ $order->service_name_snapshot ?? $order->service?->name ?? '-' }}</p>
<p>Tier: {{ $order->service_tier_name_snapshot ?? $order->serviceTier?->name ?? 'Legacy order' }}</p>
<p>Total: {{ number_format($order->total_amount) }}</p>
<p>Cancellation Reason: {{ $order->cancellation_reason ?? '—' }}</p>
<p>Provider ETA (mins): {{ $order->provider_eta_minutes ?? '—' }}</p>
<p>Provider Tracking Updated: {{ $order->provider_last_location_at ?? '—' }}</p>

<h3>Items</h3>
<table><thead><tr><th>Type</th><th>Name</th><th>Tier</th><th>Amount</th></tr></thead><tbody>
@foreach($order->items as $item)
<tr><td>{{ $item->item_type }}</td><td>{{ $item->name_snapshot }}</td><td>{{ $item->tier_name_snapshot ?? '—' }}</td><td>{{ number_format($item->line_total_amount) }}</td></tr>
@endforeach
</tbody></table>

<h3>Dispatch Offers</h3>
<table><thead><tr><th>Provider</th><th>Batch</th><th>Status</th><th>Expires</th><th>Responded</th></tr></thead><tbody>
@forelse($order->offers as $offer)
<tr>
    <td>{{ $offer->providerProfile?->display_name ?? 'Provider #'.$offer->provider_profile_id }}</td>
    <td>{{ $offer->batch_no }}</td>
    <td>{{ $offer->status }}</td>
    <td>{{ $offer->expires_at }}</td>
    <td>{{ $offer->responded_at ?? '—' }}</td>
</tr>
@empty
<tr><td colspan="5">No offers created.</td></tr>
@endforelse
</tbody></table>

<h3>Status History</h3>
<table><thead><tr><th>From</th><th>To</th><th>At</th></tr></thead><tbody>
@foreach($order->statusHistories as $history)
<tr><td>{{ $history->from_status }}</td><td>{{ $history->to_status }}</td><td>{{ $history->created_at }}</td></tr>
@endforeach
</tbody></table>
</x-app-layout>
