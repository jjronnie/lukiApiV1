<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans">
    @php($currentUser = auth()->user())
    <div class="admin-shell">
        <button data-sidebar-open type="button"
            class="fixed left-4 top-4 z-40 inline-flex h-11 w-11 items-center justify-center rounded-xl border border-white/15 bg-black/60 text-zinc-100 backdrop-blur md:hidden"
            aria-label="Open sidebar">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75Zm0 5A.75.75 0 0 1 2.75 9h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 9.75Zm0 5a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z"
                    clip-rule="evenodd" />
            </svg>
        </button>

        <div data-sidebar-overlay
            class="pointer-events-none fixed inset-0 z-40 bg-black/70 opacity-0 transition-opacity duration-300 md:hidden">
        </div>

        <aside data-sidebar class="admin-sidebar -translate-x-full md:translate-x-0">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div
                        class="grid h-11 w-11 place-items-center rounded-xl bg-gradient-to-br from-cyan-300 to-sky-400 text-black shadow-lg shadow-cyan-700/40">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path
                                d="M10 2a.75.75 0 0 1 .75.75v.79l3.25 1.625V4.75a.75.75 0 0 1 1.5 0v1.175l1.415.708a.75.75 0 0 1 0 1.342l-1.415.707v1.568a.75.75 0 0 1-1.5 0V9.432l-3.25 1.625v3.193l1.703.851a.75.75 0 1 1-.67 1.342L10 15.559l-1.703.884a.75.75 0 1 1-.67-1.342l1.703-.85v-3.194L6.08 9.432v1.568a.75.75 0 0 1-1.5 0V9.682l-1.415-.707a.75.75 0 0 1 0-1.342l1.415-.708V4.75a.75.75 0 0 1 1.5 0v.424l3.25-1.625v-.79A.75.75 0 0 1 10 2Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-display text-base">Luki Online</p>
                        <p class="text-xs text-zinc-400">Superadmin Console</p>
                    </div>
                </div>
                <button data-sidebar-close type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 text-zinc-300 md:hidden"
                    aria-label="Close sidebar">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M4.22 4.22a.75.75 0 0 1 1.06 0L10 8.94l4.72-4.72a.75.75 0 1 1 1.06 1.06L11.06 10l4.72 4.72a.75.75 0 1 1-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 1 1-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 0 1 0-1.06Z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <div class="mt-8 space-y-1">
                <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-[0.14em] text-zinc-500">Platform</p>

                <a href="{{ route('admin.dashboard') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'admin-nav-link-active' : '' }}">
                    <svg class="admin-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M3 3.75A1.75 1.75 0 0 1 4.75 2h3.5A1.75 1.75 0 0 1 10 3.75v3.5A1.75 1.75 0 0 1 8.25 9h-3.5A1.75 1.75 0 0 1 3 7.25v-3.5Zm0 9A1.75 1.75 0 0 1 4.75 11h3.5A1.75 1.75 0 0 1 10 12.75v3.5A1.75 1.75 0 0 1 8.25 18h-3.5A1.75 1.75 0 0 1 3 16.25v-3.5ZM11 3.75A1.75 1.75 0 0 1 12.75 2h3.5A1.75 1.75 0 0 1 18 3.75v3.5A1.75 1.75 0 0 1 16.25 9h-3.5A1.75 1.75 0 0 1 11 7.25v-3.5Zm1.75 8.25a1.75 1.75 0 0 0-1.75 1.75v2.5A1.75 1.75 0 0 0 12.75 18h3.5A1.75 1.75 0 0 0 18 16.25v-2.5A1.75 1.75 0 0 0 16.25 12h-3.5Z" />
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.services.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.services.*') ? 'admin-nav-link-active' : '' }}">
                    <svg class="admin-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M4.75 3A1.75 1.75 0 0 0 3 4.75v10.5C3 16.216 3.784 17 4.75 17h10.5A1.75 1.75 0 0 0 17 15.25V4.75A1.75 1.75 0 0 0 15.25 3H4.75Zm.75 4.5A.75.75 0 0 1 6.25 7h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 5.5 7.5Zm0 4A.75.75 0 0 1 6.25 11h4.5a.75.75 0 0 1 0 1.5h-4.5a.75.75 0 0 1-.75-.75Z" />
                    </svg>
                    Services
                </a>
                <a href="{{ route('admin.service-categories.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.service-categories.*') ? 'admin-nav-link-active' : '' }}">
                    <svg class="admin-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M4 4.75A1.75 1.75 0 0 1 5.75 3h2.5A1.75 1.75 0 0 1 10 4.75v2.5A1.75 1.75 0 0 1 8.25 9h-2.5A1.75 1.75 0 0 1 4 7.25v-2.5Zm6 0A1.75 1.75 0 0 1 11.75 3h2.5A1.75 1.75 0 0 1 16 4.75v2.5A1.75 1.75 0 0 1 14.25 9h-2.5A1.75 1.75 0 0 1 10 7.25v-2.5ZM4 11.75A1.75 1.75 0 0 1 5.75 10h2.5A1.75 1.75 0 0 1 10 11.75v2.5A1.75 1.75 0 0 1 8.25 16h-2.5A1.75 1.75 0 0 1 4 14.25v-2.5ZM11.5 10a.75.75 0 0 1 .75.75v1.19h1.19a.75.75 0 0 1 0 1.5h-1.19v1.19a.75.75 0 0 1-1.5 0v-1.19h-1.19a.75.75 0 0 1 0-1.5h1.19v-1.19A.75.75 0 0 1 11.5 10Z" />
                    </svg>
                    Categories
                </a>
                <a href="{{ route('admin.transport-zones.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.transport-zones.*') ? 'admin-nav-link-active' : '' }}">
                    <svg class="admin-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M10 2.75a.75.75 0 0 1 .67.414l5.5 11A.75.75 0 0 1 15.5 15.25h-11a.75.75 0 0 1-.67-1.086l5.5-11A.75.75 0 0 1 10 2.75Zm0 3.432-3.145 6.318h6.29L10 6.182Z" />
                    </svg>
                    Transport Zones
                </a>
                <a href="{{ route('admin.home-adverts.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.home-adverts.*') ? 'admin-nav-link-active' : '' }}">
                    <svg class="admin-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M3.5 4.75A1.75 1.75 0 0 1 5.25 3h9.5A1.75 1.75 0 0 1 16.5 4.75v10.5A1.75 1.75 0 0 1 14.75 17h-9.5A1.75 1.75 0 0 1 3.5 15.25V4.75Zm2.25 1a.75.75 0 0 0-.75.75v7a.75.75 0 0 0 1.28.53l2.22-2.22 1.72 1.72a.75.75 0 0 0 1.06 0l2.72-2.72 1.22 1.22a.75.75 0 0 0 1.28-.53V6.5a.75.75 0 0 0-.75-.75h-9.5Zm1.75 1.75a1 1 0 1 1 0 2 1 1 0 0 1 0-2Z" />
                    </svg>
                    Home Adverts
                </a>
                <a href="{{ route('admin.addons.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.addons.*') ? 'admin-nav-link-active' : '' }}">
                    <svg class="admin-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M3.5 5A1.5 1.5 0 0 1 5 3.5h10A1.5 1.5 0 0 1 16.5 5v3A1.5 1.5 0 0 1 15 9.5H5A1.5 1.5 0 0 1 3.5 8V5Zm0 7A1.5 1.5 0 0 1 5 10.5h10a1.5 1.5 0 0 1 1.5 1.5v3A1.5 1.5 0 0 1 15 16.5H5A1.5 1.5 0 0 1 3.5 15v-3ZM6 6.75a.75.75 0 0 0 0 1.5h2a.75.75 0 0 0 0-1.5H6Zm0 7a.75.75 0 0 0 0 1.5h2a.75.75 0 0 0 0-1.5H6Z" />
                    </svg>
                    Add-ons
                </a>
                <a href="{{ route('admin.pricing-rules.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.pricing-rules.*') ? 'admin-nav-link-active' : '' }}">
                    <svg class="admin-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M11.5 2.5a.75.75 0 0 1 .75.75V4h3.25a.75.75 0 0 1 0 1.5h-1.572l-1.9 9.498A2.25 2.25 0 0 1 9.82 17H5.75a2.25 2.25 0 0 1-2.205-2.701L5.446 5.5H3.75a.75.75 0 0 1 0-1.5H6V3.25a.75.75 0 0 1 1.5 0V4h3.25v-.75a.75.75 0 0 1 .75-.75Z" />
                    </svg>
                    Pricing Rules
                </a>
                <a href="{{ route('admin.providers.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.providers.*') ? 'admin-nav-link-active' : '' }}">
                    <svg class="admin-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M10 2a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-6 14a6 6 0 1 1 12 0v1H4v-1Zm12.5-6a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm-1.75 2.177a5.979 5.979 0 0 1 2.75 5.073V18h-1.5v-.75a4.49 4.49 0 0 0-1.726-3.573 6.05 6.05 0 0 0 .476-1.5Z" />
                    </svg>
                    Providers
                </a>
                <a href="{{ route('admin.users.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'admin-nav-link-active' : '' }}">
                    <svg class="admin-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M10 2.75a3.25 3.25 0 1 0 0 6.5 3.25 3.25 0 0 0 0-6.5ZM4.5 16a5.5 5.5 0 0 1 11 0v1.25H4.5V16Zm11-8.5a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Zm-1.515 1.322a4.983 4.983 0 0 1 2.765 4.428v1h-1.5v-1a3.49 3.49 0 0 0-1.67-2.973c.18-.45.315-.934.405-1.455Z" />
                    </svg>
                    Users
                </a>
                <a href="{{ route('admin.user-identity-verifications.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.user-identity-verifications.*') ? 'admin-nav-link-active' : '' }}">
                    <svg class="admin-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M10 2.5 4.75 4.6v4.543c0 3.545 2.174 6.749 5.25 7.857 3.076-1.108 5.25-4.312 5.25-7.857V4.6L10 2.5Zm2.03 5.72a.75.75 0 1 1 1.06 1.06l-3.2 3.2a.75.75 0 0 1-1.06 0L7.24 10.9a.75.75 0 1 1 1.06-1.06l1.06 1.06 2.67-2.68Z" />
                    </svg>
                    Customer Verification
                </a>
                <a href="{{ route('admin.orders.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.orders.*') ? 'admin-nav-link-active' : '' }}">
                    <svg class="admin-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M3.75 3A1.75 1.75 0 0 0 2 4.75v8.5C2 14.216 2.784 15 3.75 15h12.5A1.75 1.75 0 0 0 18 13.25v-8.5A1.75 1.75 0 0 0 16.25 3H3.75Zm2.5 3.25a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5h-6a.75.75 0 0 1-.75-.75Zm0 3.5a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5h-6a.75.75 0 0 1-.75-.75Zm0 3.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 0 1.5h-3a.75.75 0 0 1-.75-.75Z" />
                    </svg>
                    Orders
                </a>
                <a href="{{ route('admin.wallets.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.wallets.*') ? 'admin-nav-link-active' : '' }}">
                    <svg class="admin-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M2.5 5.75A2.75 2.75 0 0 1 5.25 3h9.5a2.75 2.75 0 0 1 2.75 2.75v.5a.75.75 0 0 1-.75.75h-1a2.25 2.25 0 0 0 0 4.5h1a.75.75 0 0 1 .75.75v1A2.75 2.75 0 0 1 14.75 16h-9.5A2.75 2.75 0 0 1 2.5 13.25v-7.5Zm11.25 2.75a.75.75 0 0 0 0 1.5h3.25v-1.5h-3.25Z" />
                    </svg>
                    Wallets
                </a>
                <a href="{{ route('admin.commission-rules.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.commission-rules.*') ? 'admin-nav-link-active' : '' }}">
                    <svg class="admin-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M10 2.5a.75.75 0 0 1 .75.75v1.247a5.25 5.25 0 0 1 2.85 8.864l.883.883a.75.75 0 1 1-1.06 1.06l-.883-.882a5.25 5.25 0 0 1-8.864-2.85H2.75a.75.75 0 0 1 0-1.5h1.247a5.25 5.25 0 0 1 2.85-8.864V3.25A.75.75 0 0 1 7.5 2.5h2.5ZM10 6a3.75 3.75 0 1 0 0 7.5A3.75 3.75 0 0 0 10 6Z" />
                    </svg>
                    Commission
                </a>
                <a href="{{ route('admin.disputes.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.disputes.*') ? 'admin-nav-link-active' : '' }}">
                    <svg class="admin-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M10 2a8 8 0 0 0-6.32 12.906l-.63 2.526a.75.75 0 0 0 .918.918l2.526-.63A8 8 0 1 0 10 2Zm-.75 4.5a.75.75 0 0 1 1.5 0v4a.75.75 0 0 1-1.5 0v-4Zm.75 7.25a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z" />
                    </svg>
                    Disputes
                </a>
            </div>

            <div class="mt-auto rounded-2xl border border-white/10 bg-white/[0.03] p-3">
                <p class="text-xs uppercase tracking-[0.14em] text-zinc-500">Account</p>
                <div class="mt-3 flex items-center gap-3">
                    <div
                        class="grid h-10 w-10 place-items-center rounded-xl bg-white/10 text-sm font-semibold text-zinc-100">
                        {{ strtoupper(substr($currentUser?->name ?? 'A', 0, 1)) }}</div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-zinc-100">{{ $currentUser?->name }}</p>
                        <p class="truncate text-xs text-zinc-400">{{ $currentUser?->email }}</p>
                    </div>
                </div>
                <form class="mt-3" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-light w-full">Sign out</button>
                </form>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-panel mb-4 flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.14em] text-zinc-500">Luki Operations</p>
                    <h1 class="mt-1 text-xl font-display">
                        {{ request()->routeIs('admin.dashboard') ? 'Superadmin Dashboard' : 'Administration' }}</h1>
                </div>
                <div class="hidden text-right sm:block">
                    <p class="text-xs uppercase tracking-[0.14em] text-zinc-500">Today</p>
                    <p class="text-sm font-medium text-zinc-300">{{ now()->format('D, d M Y') }}</p>
                </div>
            </header>

            <section class="admin-panel">
                @if (session('status'))
                    <div class="status">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div
                        class="mb-4 rounded-xl border border-rose-300/35 bg-rose-300/10 px-4 py-3 text-sm text-rose-100">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                @yield('content')
            </section>
        </main>
    </div>
</body>

</html>
