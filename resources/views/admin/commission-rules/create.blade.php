<x-app-layout>
<h1>Create Commission Rule</h1>
<form method="POST" action="{{ route('admin.commission-rules.store') }}">
    @csrf
    <label>Service (optional)</label><select name="service_id"><option value="">Global</option>@foreach($services as $service)<option value="{{ $service->id }}">{{ $service->name }}</option>@endforeach</select>
    <label>Type</label><select name="commission_type" required><option value="percentage">Percentage</option><option value="fixed">Fixed</option></select>
    <label>Value</label><input name="value" type="number" step="0.0001" required>
    <label>Min Amount</label><input name="min_commission_amount" type="number">
    <label>Max Amount</label><input name="max_commission_amount" type="number">
    <label>Effective From</label><input name="effective_from" type="datetime-local">
    <label>Effective To</label><input name="effective_to" type="datetime-local">
    <label><input type="checkbox" name="is_active" value="1" checked style="width:auto;"> Active</label>
    <button type="submit">Create</button>
</form>
</x-app-layout>
