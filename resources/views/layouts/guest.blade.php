@extends('layouts.main')


@section('content')
    {{-- nav --}}
    @include('layouts.nav')


    <main class="mb-5">
        {{ $slot }}
    </main>

    <!-- Footer -->
    @include('layouts.footer')
@endsection
