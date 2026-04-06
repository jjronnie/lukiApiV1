  <!-- Preloader -->
    <div x-data="{ loading: true }" x-init="window.addEventListener('load', () => loading = false)" x-show="loading"
        x-transition:leave="transition ease-in-out duration-700" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 flex flex-col items-center justify-center bg-gradient-to-r from-hw-blue to-hw-green z-[9999]">
        <!-- Spinner with Logo -->
        <div class="relative w-24 h-24 mb-4">
            <!-- Outer spinning ring -->
            <div class="absolute inset-0 border-4 border-hw-green border-t-transparent rounded-full animate-spin"></div>
            <!-- Inner slow spinning ring -->
            <div class="absolute inset-2 border-4 border-white border-t-transparent rounded-full animate-spin-slow">
            </div>
            <!-- Logo in center -->
            <div class="absolute inset-0 flex items-center justify-center">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" class="w-10 h-10 object-contain">
            </div>
        </div>

        <!-- Loading Text -->
        <div class="text-white text-lg font-semibold animate-pulse-scale">
            Loading...
        </div>
    </div>