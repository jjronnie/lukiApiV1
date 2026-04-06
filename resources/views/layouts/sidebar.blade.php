<aside
    class="fixed inset-y-0 left-0 z-[10002] flex w-72 flex-col bg-primary text-white shadow-2xl transition-transform duration-300 ease-in-out lg:z-[10000] lg:w-64 lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    @sidebar-close.window="sidebarOpen = false">

    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
        <div class="flex items-center gap-3">
            <div class="grid h-10 w-10 place-items-center rounded-lg bg-gradient-to-br from-cyan-400 to-sky-500 text-black shadow-lg shadow-cyan-500/30">
                <x-lucide-settings class="h-5 w-5" />
            </div>
            <div class="flex flex-col">
                <span class="text-sm font-bold tracking-wide text-white">Luki Online</span>
                <span class="text-[10px] uppercase tracking-widest text-gray-400">Superadmin</span>
            </div>
        </div>
        <button @click="sidebarOpen = false"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-white/10 hover:text-white lg:hidden"
            aria-label="Close sidebar">
            <x-lucide-x class="h-4 w-4" />
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4 no-scrollbar">

        {{-- Overview --}}
        <p class="px-3 pb-2 pt-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-gray-500">Overview</p>

        <a href="{{ route('admin.dashboard') }}"
            class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'sidebar-link-active' : '' }}">
            <x-lucide-layout-dashboard class="h-4 w-4" />
            <span>Dashboard</span>
        </a>

        {{-- Catalog --}}
        <p class="px-3 pb-2 pt-5 text-[10px] font-semibold uppercase tracking-[0.18em] text-gray-500">Catalog</p>

        <a href="{{ route('admin.services.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.services.*') ? 'sidebar-link-active' : '' }}">
            <x-lucide-clipboard-list class="h-4 w-4" />
            <span>Services</span>
        </a>

        <a href="{{ route('admin.service-categories.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.service-categories.*') ? 'sidebar-link-active' : '' }}">
            <x-lucide-grid class="h-4 w-4" />
            <span>Categories</span>
        </a>

        <a href="{{ route('admin.addons.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.addons.*') ? 'sidebar-link-active' : '' }}">
            <x-lucide-puzzle class="h-4 w-4" />
            <span>Add-ons</span>
        </a>

        <a href="{{ route('admin.pricing-rules.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.pricing-rules.*') ? 'sidebar-link-active' : '' }}">
            <x-lucide-banknote class="h-4 w-4" />
            <span>Pricing Rules</span>
        </a>

        {{-- Operations --}}
        <p class="px-3 pb-2 pt-5 text-[10px] font-semibold uppercase tracking-[0.18em] text-gray-500">Operations</p>

        <a href="{{ route('admin.orders.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'sidebar-link-active' : '' }}">
            <x-lucide-file-text class="h-4 w-4" />
            <span>Orders</span>
        </a>

        <a href="{{ route('admin.disputes.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.disputes.*') ? 'sidebar-link-active' : '' }}">
            <x-lucide-alert-circle class="h-4 w-4" />
            <span>Disputes</span>
        </a>

        <a href="{{ route('admin.commission-rules.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.commission-rules.*') ? 'sidebar-link-active' : '' }}">
            <x-lucide-percent class="h-4 w-4" />
            <span>Commission</span>
        </a>

        <a href="{{ route('admin.wallets.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.wallets.*') ? 'sidebar-link-active' : '' }}">
            <x-lucide-wallet class="h-4 w-4" />
            <span>Wallets</span>
        </a>

        {{-- People --}}
        <p class="px-3 pb-2 pt-5 text-[10px] font-semibold uppercase tracking-[0.18em] text-gray-500">People</p>

        <a href="{{ route('admin.users.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'sidebar-link-active' : '' }}">
            <x-lucide-users class="h-4 w-4" />
            <span>Users</span>
        </a>

        <a href="{{ route('admin.providers.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.providers.*') ? 'sidebar-link-active' : '' }}">
            <x-lucide-user-check class="h-4 w-4" />
            <span>Providers</span>
        </a>

        <a href="{{ route('admin.user-identity-verifications.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.user-identity-verifications.*') ? 'sidebar-link-active' : '' }}">
            <x-lucide-shield-check class="h-4 w-4" />
            <span>Verifications</span>
        </a>

        {{-- Marketing --}}
        <p class="px-3 pb-2 pt-5 text-[10px] font-semibold uppercase tracking-[0.18em] text-gray-500">Marketing</p>

        <a href="{{ route('admin.home-adverts.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.home-adverts.*') ? 'sidebar-link-active' : '' }}">
            <x-lucide-image class="h-4 w-4" />
            <span>Home Adverts</span>
        </a>

        <a href="{{ route('admin.transport-zones.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.transport-zones.*') ? 'sidebar-link-active' : '' }}">
            <x-lucide-navigation class="h-4 w-4" />
            <span>Transport Zones</span>
        </a>

    </nav>

    {{-- User Footer --}}
    @php
        $user = auth()->user();
        $initial = strtoupper(substr($user->name ?? 'A', 0, 1));
    @endphp

    <div x-cloak x-data="{ open: false }" class="border-t border-white/10 p-3">
        <button @click="open = !open"
            class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 transition-colors hover:bg-white/10">
            @if ($user->profile_photo_path)
                <img src="{{ asset('storage/' . $user->profile_photo_path) }}"
                    class="h-9 w-9 rounded-full object-cover" alt="{{ $user->name }}">
            @else
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-sm font-bold">
                    {{ $initial }}
                </div>
            @endif
            <span class="flex-1 truncate text-left text-sm font-medium">{{ $user->name }}</span>
            <x-lucide-chevron-up x-show="open" class="h-3.5 w-3.5 text-gray-400" />
            <x-lucide-chevron-down x-show="!open" class="h-3.5 w-3.5 text-gray-400" />
        </button>

        <div x-show="open" x-transition @click.outside="open = false"
            class="mt-2 overflow-hidden rounded-lg bg-white/5 shadow-lg">
            <div class="border-b border-white/10 px-4 py-2.5">
                <p class="text-sm font-medium">{{ $user->name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ $user->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-red-400 transition-colors hover:bg-red-500/10 hover:text-red-300">
                    <x-lucide-power class="h-4 w-4" />
                    Log out
                </button>
            </form>
        </div>
    </div>
</aside>
