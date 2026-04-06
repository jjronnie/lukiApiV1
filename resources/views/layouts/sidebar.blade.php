<div class="w-80 lg:w-64 bg-primary text-white flex flex-col fixed top-0 left-0 h-screen z-[10002] lg:z-[10000] transform transition-transform duration-300 -translate-x-full lg:translate-x-0"
    id="sidebar"
    x-data
    :class="{ 'translate-x-0': $store?.sidebarOpen ?? false }"
    x-bind:class="$root.closest('[x-data]').sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    @sidebar-open.window="$el.classList.remove('-translate-x-full'); $el.classList.add('translate-x-0')"
    @sidebar-close.window="$el.classList.add('-translate-x-full'); $el.classList.remove('translate-x-0')">

    <div class="sidebar-header p-4 border-b border-gray-500 m-2">
        <div class="flex items-center space-x-3">
            <div class="grid h-11 w-11 place-items-center rounded-sm bg-gradient-to-br from-cyan-300 to-sky-400 text-black shadow-lg shadow-cyan-700/40">
                <x-lucide-settings class="h-5 w-5" />
            </div>
            <div class="flex flex-col">
                <span class="text-center whitespace-nowrap text-white font-bold">Luki Online</span>
                <span class="text-xs text-center text-gray-400">Superadmin Console</span>
            </div>
        </div>
        <button @click="$dispatch('sidebar-close')" class="lg:hidden p-1 rounded-md hover:bg-blue-900 transition-colors" id="closeSidebar">
            <x-lucide-x class="w-4 h-4" />
        </button>
    </div>

    <div class="flex-1 overflow-y-auto no-scrollbar">
        <nav class="p-4 space-y-1">
            <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-[0.14em] text-gray-400">Platform</p>

            <a href="{{ route('admin.dashboard') }}"
                class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'sidebar-link-active' : '' }}">
                <x-lucide-layout-dashboard class="w-4 h-4" />
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.services.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.services.*') ? 'sidebar-link-active' : '' }}">
                <x-lucide-clipboard-list class="w-4 h-4" />
                <span>Services</span>
            </a>
            <a href="{{ route('admin.service-categories.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.service-categories.*') ? 'sidebar-link-active' : '' }}">
                <x-lucide-grid class="w-4 h-4" />
                <span>Categories</span>
            </a>
            <a href="{{ route('admin.transport-zones.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.transport-zones.*') ? 'sidebar-link-active' : '' }}">
                <x-lucide-navigation class="w-4 h-4" />
                <span>Transport Zones</span>
            </a>
            <a href="{{ route('admin.home-adverts.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.home-adverts.*') ? 'sidebar-link-active' : '' }}">
                <x-lucide-image class="w-4 h-4" />
                <span>Home Adverts</span>
            </a>
            <a href="{{ route('admin.addons.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.addons.*') ? 'sidebar-link-active' : '' }}">
                <x-lucide-puzzle class="w-4 h-4" />
                <span>Add-ons</span>
            </a>
            <a href="{{ route('admin.pricing-rules.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.pricing-rules.*') ? 'sidebar-link-active' : '' }}">
                <x-lucide-banknote class="w-4 h-4" />
                <span>Pricing Rules</span>
            </a>
            <a href="{{ route('admin.providers.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.providers.*') ? 'sidebar-link-active' : '' }}">
                <x-lucide-user-check class="w-4 h-4" />
                <span>Providers</span>
            </a>
            <a href="{{ route('admin.users.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'sidebar-link-active' : '' }}">
                <x-lucide-users class="w-4 h-4" />
                <span>Users</span>
            </a>
            <a href="{{ route('admin.user-identity-verifications.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.user-identity-verifications.*') ? 'sidebar-link-active' : '' }}">
                <x-lucide-shield-check class="w-4 h-4" />
                <span>Customer Verification</span>
            </a>
            <a href="{{ route('admin.orders.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'sidebar-link-active' : '' }}">
                <x-lucide-file-text class="w-4 h-4" />
                <span>Orders</span>
            </a>
            <a href="{{ route('admin.wallets.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.wallets.*') ? 'sidebar-link-active' : '' }}">
                <x-lucide-wallet class="w-4 h-4" />
                <span>Wallets</span>
            </a>
            <a href="{{ route('admin.commission-rules.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.commission-rules.*') ? 'sidebar-link-active' : '' }}">
                <x-lucide-percent class="w-4 h-4" />
                <span>Commission</span>
            </a>
            <a href="{{ route('admin.disputes.index') }}"
                class="sidebar-link {{ request()->routeIs('admin.disputes.*') ? 'sidebar-link-active' : '' }}">
                <x-lucide-alert-circle class="w-4 h-4" />
                <span>Disputes</span>
            </a>
        </nav>
    </div>

    @php
        $user = auth()->user();
        $initial = strtoupper(substr($user->name ?? 'A', 0, 1));
    @endphp

    <div x-cloak x-data="{ open: false }" class="mt-auto p-3 border-t border-white/10">
        <button @click="open = !open"
            class="w-full flex items-center gap-3 bg-white/5 hover:bg-white/10 rounded-lg px-3 py-2 transition">
            @if ($user->profile_photo_path)
                <img src="{{ asset('storage/' . $user->profile_photo_path) }}"
                    class="w-10 h-10 rounded-full object-cover" alt="{{ $user->name }}">
            @else
                <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center font-bold">
                    {{ $initial }}
                </div>
            @endif
            <span class="flex-1 text-sm font-semibold truncate text-left">{{ $user->name }}</span>
            <x-lucide-chevron-up x-show="open" class="w-4 h-4" />
            <x-lucide-chevron-down x-show="!open" class="w-4 h-4" />
        </button>

        <div x-show="open" x-transition @click.outside="open = false"
            class="mt-2 bg-white/5 hover:bg-white/10 rounded-lg shadow-lg overflow-hidden">
            <div class="px-4 py-3 border-b border-white/10">
                <p class="text-sm font-semibold">{{ $user->name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ $user->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-2 px-4 py-2 hover:bg-red-600 text-sm text-left">
                    <x-lucide-power class="w-4 h-4" />
                    Log out
                </button>
            </form>
        </div>
    </div>
</div>
