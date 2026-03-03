@extends('layouts.admin')

@section('content')
<h1>Create Pricing Rule</h1>
<form method="POST" action="{{ route('admin.pricing-rules.store') }}">
    @csrf
    <label>Service (optional)</label>
    <select name="service_id"><option value="">Global</option>@foreach($services as $service)<option value="{{ $service->id }}">{{ $service->name }}</option>@endforeach</select>
    <label>Rule Type</label>
    <select name="rule_type" required>
        <option value="distance_per_km">Distance Per KM</option><option value="distance_band">Distance Band</option>
        <option value="tax_percentage">Tax Percentage</option><option value="peak_hours">Peak Hours</option><option value="overtime">Overtime</option>
    </select>
    <label>Priority</label><input type="number" name="priority" value="100">
    <label>Config JSON</label><textarea name="config" required>{}</textarea>
    <label><input type="checkbox" name="is_active" value="1" checked style="width:auto;"> Active</label>
    <button type="submit">Create</button>
</form>
@endsection
