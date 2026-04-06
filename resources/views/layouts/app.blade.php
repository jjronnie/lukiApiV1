@extends('layouts.main')
@section('content')
    @php($currentUser = auth()->user())
    <div class="admin-shell" x-data="{ sidebarOpen: false }">
        <button @click="sidebarOpen = true" type="button"
            class="fixed left-3 top-3 z-[10001] inline-flex h-10 w-10 items-center justify-center rounded-lg border border-white/15 bg-black/60 text-zinc-100 backdrop-blur lg:hidden"
            aria-label="Open sidebar">
            <x-lucide-menu class="h-5 w-5" />
        </button>

        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false"
            class="pointer-events-none fixed inset-0 z-[10001] bg-black/70 lg:hidden"
            :class="sidebarOpen ? 'pointer-events-auto' : ''">
        </div>

        @include('layouts.sidebar')

        <div class="lg:ml-64">
            @include('layouts.nav')

            <main class="p-6">
                @if (session('status'))
                    <div class="status">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div
                        class="mb-4 rounded-sm border border-rose-300/35 bg-rose-300/10 px-4 py-3 text-sm text-rose-100">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
@endsection
