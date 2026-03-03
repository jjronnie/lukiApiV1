@extends('layouts.admin')

@section('content')
<div class="actions"><h1>Commission Rules</h1><a class="btn" href="{{ route('admin.commission-rules.create') }}">New Rule</a></div>
<table><thead><tr><th>Service</th><th>Type</th><th>Value</th><th>Active</th><th></th></tr></thead><tbody>
@foreach($rules as $rule)
<tr>
    <td>{{ $rule->service?->name ?? 'Global' }}</td>
    <td>{{ $rule->commission_type->value ?? $rule->commission_type }}</td>
    <td>{{ $rule->value }}</td>
    <td>{{ $rule->is_active ? 'Yes' : 'No' }}</td>
    <td><a class="btn btn-light" href="{{ route('admin.commission-rules.edit', $rule) }}">Edit</a></td>
</tr>
@endforeach
</tbody></table>
{{ $rules->links() }}
@endsection
