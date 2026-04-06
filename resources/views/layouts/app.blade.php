@extends('layouts.main')
@section('content')
    <div class="admin-shell" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
        <button @click="sidebarOpen = true" type="button"
            class="fixed left-4 top-4 z-[10001] inline-flex h-11 w-11 items-center justify-center rounded-xl bg-primary/90 text-white shadow-lg shadow-black/20 backdrop-blur transition-all duration-200 hover:bg-primary hover:scale-105 active:scale-95 lg:hidden"
            aria-label="Open sidebar">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <div x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-[10001] bg-black/60 backdrop-blur-sm lg:hidden"
            :class="sidebarOpen ? '' : 'pointer-events-none'">
        </div>

        @include('layouts.sidebar')

        <div class="lg:ml-64">
            @include('layouts.nav')

            <main class="pt-24 px-4 pb-8 sm:px-6 lg:px-8 lg:pt-24">
                @if (session('status'))
                    <div class="status">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div
                        class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700 shadow-sm">
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
