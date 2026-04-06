@props([
    'title' => '',
    'sub_title' => '',
    'value' => '',
    'icon' => '',
    'color' => 'blue',
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-sm shadow-sm  p-6 border border-gray-200']) }}>
    <div class="flex items-center">
        @if ($icon)
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-blue-600 rounded-sm  flex items-center justify-center">
                    <x-dynamic-component :component="'lucide-'.$icon" class="w-5 h-5 text-white" />
                </div>


            </div>
        @endif
        <div class="ml-4">
            {{-- Only display if value is provided --}}
            @if ($value)
                <p class="text-xl font-bold text-gray-800">{{ $value }}</p>
            @endif

            {{-- Only display if title is provided --}}
            @if ($title)
                <h3 class="text-1xl font-semibold text-gray-600">{{ $title }}</h3>
            @endif

            {{-- Only display if sub_title is provided --}}
            @if ($sub_title)
                <p class="text-sm text-muted text-gray-500">{{ $sub_title }}</p>
            @endif
        </div>
    </div>


</div>
