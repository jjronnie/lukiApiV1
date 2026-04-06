@props([
    'title' => '',
    'subtitle' => null,
    'actions' => null,
    'breadcrumb' => null,
])

<div class="mb-8">
    @if ($breadcrumb)
        <nav class="mb-3 flex items-center gap-1.5 text-xs text-gray-500">
            {{ $breadcrumb }}
        </nav>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
            @endif
        </div>

        @if ($actions)
            <div class="flex flex-wrap items-center gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
