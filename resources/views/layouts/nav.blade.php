<header class="mb-4 flex items-center justify-between gap-4 px-2 py-2" x-data="{ quickAccessOpen: false, notificationOpen: false }">
    <!-- compact header: branding removed for a minimal look -->
    <div class="hidden md:block"></div>

    <div class="ml-auto flex items-center space-x-2 relative">
        <div class="relative" @click.away="notificationOpen = false">
            <button class="p-1.5 rounded-md hover:bg-white/10 transition-colors"
                @click="notificationOpen = !notificationOpen">
                <x-lucide-bell class="w-4 h-4" />
                <span
                    class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] px-1 py-0.5 rounded-full leading-none">0</span>
            </button>
            <div x-show="notificationOpen" x-transition x-cloak
                class="absolute right-0 top-full mt-2 w-80 bg-white rounded-sm shadow-xl border border-gray-200 z-30 p-4"
                @click.away="notificationOpen = false">
                <h3 class="text-sm text-center font-semibold text-gray-800 mb-4"></h3>
                <p class="text-center">No Notifications Found</p>
            </div>
        </div>

        <div x-data="{ open: false, showLogoutModal: false }" class="relative">
            <button @click="open = !open" class="flex items-center space-x-2 pl-1 focus:outline-none">
                <div class="p-0.5 rounded-md hover:bg-white/10 transition-colors">
                    <x-lucide-circle-user-round class="w-5 h-5" />
                </div>
            </button>
            <div x-show="open" @click.away="open = false" x-transition x-cloak
                class="absolute right-0 mt-2 w-72 bg-white text-primary rounded-lg shadow-xl z-30">
                <div class="flex items-center space-x-3 p-4 border-b">
                    <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-gray-600">
                        <x-lucide-circle-user-round class="w-8 h-8" />
                    </div>
                    <div>
                        <p class="font-semibold">{{ ucfirst(auth()->user()->name) ?? '' }}</p>
                        <p class="text-sm text-gray-500">{{ auth()->user()->email ?? '' }}</p>
                    </div>
                </div>
                <nav class="py-2">
                    <a href="#" class="flex items-center px-4 py-2 text-sm hover:bg-gray-100">
                        <x-lucide-bolt class="w-4 h-4 mr-2" /> Settings
                    </a>
                </nav>
                <button @click="showLogoutModal = true; open = false"
                    class="w-full flex items-center px-4 py-2 text-sm hover:bg-gray-100 text-red-600 border-t">
                    <x-lucide-log-out class="w-4 h-4 mr-2" /> Log out
                </button>
            </div>

            <div x-show="showLogoutModal" x-transition x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-6" @click.away="showLogoutModal = false">
                    <h2 class="text-lg font-semibold text-gray-800">Confirm Logout</h2>
                    <p class="text-sm text-gray-600 mt-2">Are you sure you want to logout?</p>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button @click="showLogoutModal = false"
                            class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 rounded">Cancel</button>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
