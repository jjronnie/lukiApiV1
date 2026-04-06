 @php
        $user = auth()->user();
        $initial = strtoupper(substr($user->name, 0, 1));
    @endphp

    <div x-cloak x-data="{ open: false }" class="mt-auto p-3 border-t border-white/10">
        <!-- Trigger -->
        <button @click="open = !open"
            class="w-full flex items-center gap-3 bg-white/5 hover:bg-white/10 rounded-lg px-3 py-2 transition">

            <!-- Avatar -->
            @if ($user->profile_photo_path)
                <img src="{{ asset('storage/' . $user->profile_photo_path) }}"
                    class="w-10 h-10 rounded-full object-cover" alt="{{ $user->name }}">
            @else
                <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center font-bold">
                    {{ $initial }}
                </div>
            @endif

            <!-- Name -->
            <span class="flex-1 text-sm font-semibold truncate text-left">
                {{ $user->name }}
            </span>

            <i data-lucide="chevron-up" x-show="open" class="w-4 h-4"></i>
            <i data-lucide="chevron-down" x-show="!open" class="w-4 h-4"></i>
        </button>

        <!-- Dropdown -->
        <div x-show="open" x-transition @click.outside="open = false"
            class="mt-2 bg-white/5 hover:bg-white/10 rounded-lg shadow-lg overflow-hidden">

            <div class="px-4 py-3 border-b border-white/10">
                <p class="text-sm font-semibold">{{ $user->name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ $user->email }}</p>
            </div>

            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-white/10 text-sm">
                <i data-lucide="user" class="w-4 h-4"></i>
                Profile
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-2 px-4 py-2 hover:bg-red-600 text-sm text-left">
                    <i data-lucide="power" class="w-4 h-4"></i>
                    Log out
                </button>
            </form>
        </div>
    </div>