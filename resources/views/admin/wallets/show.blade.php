<x-app-layout>
<h1>Wallet {{ $wallet->public_id }}</h1>
<p>Provider: {{ $wallet->providerProfile->display_name }}</p>
<p>Balance: {{ number_format($wallet->balance_amount) }}</p>

<h3>Adjust Wallet</h3>
<form method="POST" action="{{ route('admin.wallets.adjust', $wallet) }}">
    @csrf
    <label>Type</label>
    <select name="type" required>
        <option value="topup">Topup</option>
        <option value="adjustment">Adjustment</option>
        <option value="penalty">Penalty</option>
        <option value="payout">Payout</option>
    </select>
    <label>Amount (use negative for deduction)</label>
    <input type="number" name="amount" required>
    <label>Reference</label>
    <input name="reference">
    <button type="submit">Apply</button>
</form>

<h3>Transactions</h3>
<table><thead><tr><th>Date</th><th>Type</th><th>Amount</th><th>Balance After</th><th>Reference</th></tr></thead><tbody>
@foreach($wallet->transactions as $transaction)
<tr>
    <td>{{ $transaction->created_at }}</td>
    <td>{{ $transaction->type }}</td>
    <td>{{ number_format($transaction->amount) }}</td>
    <td>{{ number_format($transaction->balance_after) }}</td>
    <td>{{ $transaction->reference }}</td>
</tr>
@endforeach
</tbody></table>
</x-app-layout>
