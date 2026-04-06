@extends('layouts.main')
@section('content')
    <!-- Sidebar -->
    @include('layouts.sidebar')


    <!-- Main Content -->
    <div class="lg:ml-64">
        <!-- Header -->

        @include('layouts.nav')


        <!-- Dashboard Content -->
        <main class="p-6">
            {{ $slot }}

        </main>
    </div>
@endsection
