@extends('layouts.verification')

@php
    $pageTitle = $title ?? 'Invalid verification link';
@endphp

@section('content')
    <section class="hero">
        <p class="eyebrow">Identity Verification</p>
        <h1>{{ $title ?? 'Invalid verification link' }}</h1>
        <p class="subtitle">{{ $message ?? 'This verification link is not valid.' }}</p>
    </section>

    <section class="card stack">
        <div class="notice danger">
            {{ $message ?? 'This verification link is not valid.' }}
        </div>
        <p class="footer-note">
            Open a fresh verification session from the app instead of reusing an old link.
        </p>
    </section>
@endsection
