<header class="fixed right-0 top-0 z-[9999] flex h-16 w-full items-center justify-between gap-4 border-b border-white/10 bg-primary/95 px-4 shadow-lg backdrop-blur lg:w-[calc(100%-16rem)] lg:px-6"
    x-data="{ quickAccessOpen: false, notificationOpen: false }">

    <div class="hidden md:block"></div>

    <div class="ml-auto flex items-center gap-2">
        {{-- Notifications --}}
        <div class="relative" @click.away="notificationOpen = false">
            <button class="relative flex h-9 w-9 items-center justify-center rounded-lg text-gray-300 transition-colors hover:bg-white/10 hover:text-white"
                @click="notificationOpen = !notificationOpen">
                <x-lucide-bell class="h-5 w-5" />
                <span
                    class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white">0</span>
            </button>
            <div x-show="notificationOpen" x-transition x-cloak
                class="absolute right-0 top-full mt-2 w-80 rounded-xl border border-gray-200 bg-white shadow-2xl p-4 z-50"
                @click.away="notificationOpen = false">
                <h3 class="mb-3 text-center text-sm font-semibold text-gray-800">Notifications</h3>
                <p class="text-center text-sm text-gray-500">No Notifications Found</p>
            </div>
        </div>

        {{-- User Menu --}}
        <div x-data="{ open: false, showLogoutModal: false }" class="relative">
            <button @click="open = !open"
                class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-300 transition-colors hover:bg-white/10 hover:text-white">
                <x-lucide-circle-user-round class="h-6 w-6" />
            </button>
            <div x-show="open" @click.away="open = false" x-transition x-cloak
                class="absolute right-0 mt-2 w-72 overflow-hidden rounded-xl bg-white text-primary shadow-2xl z-50">
                <div class="flex items-center gap-3 border-b border-gray-100 p-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-500">
                        <x-lucide-circle-user-round class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold">{{ ucfirst(auth()->user()->name) ?? '' }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->email ?? '' }}</p>
                    </div>
                </div>
                <nav class="py-1">
                    <a href="#" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 transition-colors hover:bg-gray-50">
                        <x-lucide-bolt class="h-4 w-4" /> Settings
                    </a>
                </nav>
                <button @click="showLogoutModal = true; open = false"
                    class="flex w-full items-center gap-2 border-t border-gray-100 px-4 py-2.5 text-sm text-red-600 transition-colors hover:bg-red-50">
                    <x-lucide-log-out class="h-4 w-4" /> Log out
                </button>
            </div>

            {{-- Logout Confirmation Modal --}}
            <div x-show="showLogoutModal" x-transition x-cloak
                class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 backdrop-blur-sm">
                <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl" @click.away="showLogoutModal = false">
                    <h2 class="text-lg font-semibold text-gray-800">Confirm Logout</h2>
                    <p class="mt-2 text-sm text-gray-600">Are you sure you want to logout?</p>
                    <div class="mt-4 flex justify-end gap-2">
                        <button @click="showLogoutModal = false"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100">Cancel</button>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-white bg-red-600 transition-colors hover:bg-red-700">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
