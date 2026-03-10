@php
    $rows = collect($tierRows ?? [])
        ->map(function ($tier) {
            return [
                'id' => $tier['id'] ?? null,
                'name' => $tier['name'] ?? '',
                'price_amount' => $tier['price_amount'] ?? 0,
                'description' => $tier['description'] ?? '',
                'sort_order' => $tier['sort_order'] ?? 0,
                'is_active' => (bool) ($tier['is_active'] ?? false),
            ];
        })
        ->values();

    if ($rows->isEmpty()) {
        $rows = collect([
            ['name' => 'Saver', 'price_amount' => 0, 'description' => '', 'sort_order' => 1, 'is_active' => true],
            ['name' => 'Standard', 'price_amount' => 0, 'description' => '', 'sort_order' => 2, 'is_active' => true],
            ['name' => 'Premium', 'price_amount' => 0, 'description' => '', 'sort_order' => 3, 'is_active' => true],
        ]);
    }
@endphp

<div style="margin-top: 24px;">
    <div class="actions">
        <h2>Service Tiers</h2>
        <button type="button" class="btn btn-light" data-add-tier="{{ $repeaterId }}">Add Tier</button>
    </div>
    <p style="margin-bottom: 16px;">Customers book a tier first. The lowest active tier price becomes the customer app starting price.</p>

    <div id="{{ $repeaterId }}" data-tier-list style="display: grid; gap: 16px;">
        @foreach($rows as $index => $tier)
            <div data-tier-row style="border: 1px solid rgba(148, 163, 184, 0.35); border-radius: 14px; padding: 16px;">
                <input type="hidden" name="tiers[{{ $index }}][id]" value="{{ $tier['id'] }}">
                <div style="display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                    <div>
                        <label>Tier Name</label>
                        <input name="tiers[{{ $index }}][name]" value="{{ $tier['name'] }}" required>
                    </div>
                    <div>
                        <label>Price Amount</label>
                        <input type="number" min="0" name="tiers[{{ $index }}][price_amount]" value="{{ $tier['price_amount'] }}" required>
                    </div>
                    <div>
                        <label>Sort Order</label>
                        <input type="number" min="0" name="tiers[{{ $index }}][sort_order]" value="{{ $tier['sort_order'] }}">
                    </div>
                </div>
                <div style="margin-top: 12px;">
                    <label>Description</label>
                    <textarea name="tiers[{{ $index }}][description]">{{ $tier['description'] }}</textarea>
                </div>
                <div style="margin-top: 12px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                    <label><input type="checkbox" name="tiers[{{ $index }}][is_active]" value="1" {{ $tier['is_active'] ? 'checked' : '' }} style="width:auto;"> Active</label>
                    <button type="button" class="btn btn-light" data-remove-tier>Remove</button>
                </div>
            </div>
        @endforeach
    </div>
</div>

<template id="{{ $repeaterId }}-template">
    <div data-tier-row style="border: 1px solid rgba(148, 163, 184, 0.35); border-radius: 14px; padding: 16px;">
        <input type="hidden" name="tiers[__INDEX__][id]" value="">
        <div style="display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
            <div>
                <label>Tier Name</label>
                <input name="tiers[__INDEX__][name]" required>
            </div>
            <div>
                <label>Price Amount</label>
                <input type="number" min="0" name="tiers[__INDEX__][price_amount]" value="0" required>
            </div>
            <div>
                <label>Sort Order</label>
                <input type="number" min="0" name="tiers[__INDEX__][sort_order]" value="0">
            </div>
        </div>
        <div style="margin-top: 12px;">
            <label>Description</label>
            <textarea name="tiers[__INDEX__][description]"></textarea>
        </div>
        <div style="margin-top: 12px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
            <label><input type="checkbox" name="tiers[__INDEX__][is_active]" value="1" checked style="width:auto;"> Active</label>
            <button type="button" class="btn btn-light" data-remove-tier>Remove</button>
        </div>
    </div>
</template>

<script>
(() => {
    const repeaterId = @json($repeaterId);
    const container = document.getElementById(repeaterId);
    const template = document.getElementById(`${repeaterId}-template`);
    if (!container || !template || container.dataset.bound === 'true') {
        return;
    }

    container.dataset.bound = 'true';

    const nextIndex = () => container.querySelectorAll('[data-tier-row]').length;

    document.addEventListener('click', (event) => {
        const addButton = event.target.closest(`[data-add-tier="${repeaterId}"]`);
        if (addButton) {
            const markup = template.innerHTML.replace(/__INDEX__/g, String(nextIndex()));
            container.insertAdjacentHTML('beforeend', markup);
            return;
        }

        const removeButton = event.target.closest('[data-remove-tier]');
        if (!removeButton || !container.contains(removeButton)) {
            return;
        }

        const rows = container.querySelectorAll('[data-tier-row]');
        if (rows.length === 1) {
            return;
        }

        removeButton.closest('[data-tier-row]')?.remove();
    });
})();
</script>
