@extends('layouts.admin')

@section('content')
<h1>Disputes</h1>
<table><thead><tr><th>ID</th><th>Order</th><th>User</th><th>Category</th><th>Status</th><th></th></tr></thead><tbody>
@foreach($disputes as $dispute)
<tr>
    <td>{{ $dispute->id }}</td>
    <td>{{ $dispute->order->public_id }}</td>
    <td>{{ $dispute->user->email }}</td>
    <td>{{ $dispute->category->value ?? $dispute->category }}</td>
    <td>{{ $dispute->status->value ?? $dispute->status }}</td>
    <td>
        <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}">
            @csrf
            <select name="status" required><option value="resolved">Resolved</option><option value="rejected">Rejected</option></select>
            <input name="wallet_adjustment_amount" type="number" placeholder="Wallet adj (optional)">
            <textarea name="resolution_notes" placeholder="Resolution notes" required></textarea>
            <button type="submit">Submit</button>
        </form>
    </td>
</tr>
@endforeach
</tbody></table>
{{ $disputes->links() }}
@endsection
