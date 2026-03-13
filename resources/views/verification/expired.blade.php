@extends('layouts.verification')

@php
    $pageTitle = $title ?? 'Session expired';
@endphp

@section('content')
    <section class="hero">
        <p class="eyebrow">Identity Verification</p>
        <h1>{{ $title ?? 'Session expired' }}</h1>
        <p class="subtitle">{{ $message ?? 'This verification session has expired.' }}</p>
    </section>

    <section class="card stack">
        <div class="notice warning">
            {{ $message ?? 'This verification session has expired.' }}
        </div>
        <p class="footer-note">
            Return to the app and start a new verification session when you are ready.
        </p>
    </section>
@endsection
