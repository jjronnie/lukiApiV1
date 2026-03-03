@extends('layouts.admin')

@section('content')
<h1>Edit Commission Rule</h1>
<form method="POST" action="{{ route('admin.commission-rules.update', $rule) }}">
    @csrf
    @method('PUT')
    <label>Service (optional)</label><select name="service_id"><option value="">Global</option>@foreach($services as $service)<option value="{{ $service->id }}" {{ (string)$rule->service_id === (string)$service->id ? 'selected' : '' }}>{{ $service->name }}</option>@endforeach</select>
    <label>Type</label><select name="commission_type" required><option value="percentage" {{ ($rule->commission_type->value ?? $rule->commission_type) === 'percentage' ? 'selected' : '' }}>Percentage</option><option value="fixed" {{ ($rule->commission_type->value ?? $rule->commission_type) === 'fixed' ? 'selected' : '' }}>Fixed</option></select>
    <label>Value</label><input name="value" type="number" step="0.0001" value="{{ $rule->value }}" required>
    <label>Min Amount</label><input name="min_commission_amount" type="number" value="{{ $rule->min_commission_amount }}">
    <label>Max Amount</label><input name="max_commission_amount" type="number" value="{{ $rule->max_commission_amount }}">
    <label>Effective From</label><input name="effective_from" type="datetime-local" value="{{ optional($rule->effective_from)->format('Y-m-d\\TH:i') }}">
    <label>Effective To</label><input name="effective_to" type="datetime-local" value="{{ optional($rule->effective_to)->format('Y-m-d\\TH:i') }}">
    <label><input type="checkbox" name="is_active" value="1" {{ $rule->is_active ? 'checked' : '' }} style="width:auto;"> Active</label>
    <button type="submit">Save</button>
</form>
@endsection
