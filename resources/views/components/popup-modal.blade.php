<div x-cloak x-data="{ open: false }" class="inline-block">

    {{-- Default Trigger (unless user overrides slot:trigger) --}}
    @isset($trigger)
        <div @click="open = true">
            {{ $trigger }}
        </div>
    @else
        <button @click="open = true" class="btn">
            <span>{{ $buttonText }}</span>

            @if ($buttonIcon)
                <i data-lucide="{{ $buttonIcon }}" class="w-4 h-4 ml-1 mr-1 text-white"></i>
            @endif
        </button>
    @endisset

    {{-- Overlay --}}
    <div x-show="open" x-cloak @click="open = false" class="fixed inset-0 bg-black bg-opacity-40 z-40 "
        x-transition.opacity></div>

    {{-- Modal --}}
    <div x-show="open" x-cloak class="fixed inset-0 flex items-center justify-center  z-50 p-4">
        <div @click.stop x-transition:enter="transform transition-all duration-300 ease-out"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transform transition-all duration-300 ease-in"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded shadow-xl max-w-lg w-full">

            {{-- Header --}}
            <div class="flex items-center justify-between border-b px-6 py-4">
                <h2 class="text-xl font-semibold">{{ $title }}</h2>

                <button @click="open = false" class="text-white bg-red-600 p-2 rounded-lg hover:bg-red-700"  title="Close">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Content --}}
            <div class="px-6 py-5 max-h-[70vh] overflow-y-auto">
                {{ $slot }}
            </div>

        </div>
    </div>
</div>
