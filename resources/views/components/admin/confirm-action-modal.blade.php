@props([
    'id',
    'title' => 'Confirm action',
    'heading' => 'Please confirm',
    'description' => 'Are you sure you want to continue?',
    'confirmLabel' => 'Confirm',
    'cancelLabel' => 'Cancel',
])

<div id="{{ $id }}" data-confirm-modal class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/70" data-confirm-modal-backdrop></div>
    <div class="relative w-full max-w-md rounded-[28px] border border-white/10 bg-[#111827] p-6 text-zinc-100 shadow-2xl shadow-black/40">
        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-zinc-500">{{ $title }}</p>
        <h3 class="mt-3 text-xl font-semibold" data-confirm-modal-heading>{{ $heading }}</h3>
        <p class="mt-3 text-sm leading-6 text-zinc-300" data-confirm-modal-description>{{ $description }}</p>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" class="btn btn-light" data-confirm-modal-cancel>{{ $cancelLabel }}</button>
            <button type="button" class="btn" data-confirm-modal-confirm>{{ $confirmLabel }}</button>
        </div>
    </div>
</div>
