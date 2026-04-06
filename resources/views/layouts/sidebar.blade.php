<div class="w-80 lg:w-64 bg-primary text-white flex flex-col fixed top-0 left-0 h-screen z-40 lg:z-[10000] transform transition-transform duration-300 -translate-x-full lg:translate-x-0"
    id="sidebar">
    <!-- Sidebar Header -->

    <div class="sidebar-header p-4 border-b border-gray-500 m-2">


        <div class="flex items-center space-x-3">
            <div class="w-14 h-14  rounded-lg flex items-center justify-center text-white font-bold text-lg">
                <span>
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('assets/img/logo.png') }}" alt="">
                    </a>
                </span>
            </div>
            <div class="flex flex-col">
                <span class="text-center whitespace-nowrap text-white font-bold">HealingWay IVF</span>

                <span class="text-xs text-center">Where healing starts</span>
            </div>
        </div>
        <button class="lg:hidden p-1 rounded-md hover:bg-blue-900 transition-colors" id="closeSidebar">

            <i data-lucide="x" class="w-4 h-4  "></i>
        </button>
    </div>

    <!-- Scrollable Navigation Area -->



    <div class="flex-1 overflow-y-auto no-scrollbar">
        <nav class="p-4 space-y-1">
            {{-- Dashboard --}}

            {{-- Dashboard --}}
            <a href="{{ route('admin.dashboard') }}"
                class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'sidebar-link-active' : '' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4 "></i>
                <span>Dashboard</span>
            </a>
            <div class="space-y-1">

                <div class="flex items-center pt-4">
                    <span class="text-gray-200 text-sm mr-4 ml-4">System MGT</span>
                    <div class="flex-1 border-t border-gray-500 mr-4"></div>
                </div>




                {{-- <a href="{{ route('admin.appointments.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.appointments.*') ? 'sidebar-link-active' : '' }}">

                    <i data-lucide="calendar-clock" class="w-4 h-4 "></i>
                    <span>Appointments</span>
                </a> --}}













            </div>
        </nav>

    </div>


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

            <a href="#" class="flex items-center gap-2 px-4 py-2 hover:bg-white/10 text-sm">
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



</div>
