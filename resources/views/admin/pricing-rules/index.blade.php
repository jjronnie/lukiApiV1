<x-app-layout>
<div class="actions"><h1>Pricing Rules</h1><a class="btn" href="{{ route('admin.pricing-rules.create') }}">New Rule</a></div>
<table><thead><tr><th>Service</th><th>Type</th><th>Priority</th><th>Status</th><th></th></tr></thead><tbody>
@foreach($rules as $rule)
<tr>
    <td>{{ $rule->service?->name ?? 'Global' }}</td>
    <td>{{ $rule->rule_type->value ?? $rule->rule_type }}</td>
    <td>{{ $rule->priority }}</td>
    <td>{{ $rule->is_active ? 'Active' : 'Inactive' }}</td>
    <td><a class="btn btn-light" href="{{ route('admin.pricing-rules.edit', $rule) }}">Edit</a></td>
</tr>
@endforeach
</tbody></table>
{{ $rules->links() }}
</x-app-layout>
