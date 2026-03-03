@extends('layouts.admin')

@section('content')
<h1>Edit Pricing Rule</h1>
<form method="POST" action="{{ route('admin.pricing-rules.update', $rule) }}">
    @csrf
    @method('PUT')
    <label>Service (optional)</label>
    <select name="service_id"><option value="">Global</option>@foreach($services as $service)<option value="{{ $service->id }}" {{ (string) $rule->service_id === (string) $service->id ? 'selected' : '' }}>{{ $service->name }}</option>@endforeach</select>
    <label>Rule Type</label>
    <select name="rule_type" required>
        @foreach(['distance_per_km','distance_band','tax_percentage','peak_hours','overtime'] as $type)
            <option value="{{ $type }}" {{ ($rule->rule_type->value ?? $rule->rule_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
        @endforeach
    </select>
    <label>Priority</label><input type="number" name="priority" value="{{ $rule->priority }}">
    <label>Config JSON</label><textarea name="config" required>{{ json_encode($rule->config) }}</textarea>
    <label><input type="checkbox" name="is_active" value="1" {{ $rule->is_active ? 'checked' : '' }} style="width:auto;"> Active</label>
    <button type="submit">Save</button>
</form>
@endsection
