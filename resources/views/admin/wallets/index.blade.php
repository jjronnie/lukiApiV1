<x-app-layout>
<h1>Wallets</h1>
<table><thead><tr><th>Provider</th><th>Balance</th><th>Hold</th><th>Min Required</th><th>Status</th><th></th></tr></thead><tbody>
@foreach($wallets as $wallet)
<tr>
    <td>{{ $wallet->providerProfile->display_name }}</td>
    <td>{{ number_format($wallet->balance_amount) }}</td>
    <td>{{ number_format($wallet->hold_amount) }}</td>
    <td>{{ number_format($wallet->min_required_amount) }}</td>
    <td>{{ $wallet->status->value ?? $wallet->status }}</td>
    <td><a class="btn" href="{{ route('admin.wallets.show', $wallet) }}">View</a></td>
</tr>
@endforeach
</tbody></table>
{{ $wallets->links() }}
</x-app-layout>
