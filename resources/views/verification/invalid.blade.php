@extends('layouts.verification')

@php
    $pageTitle = $title ?? 'Invalid verification link';
@endphp

@section('content')
    <section class="card stack">
        <div style="display:grid; gap:12px; text-align:center;">
            <p class="eyebrow" style="margin-bottom:0;">Verification</p>
            <h1>{{ $title ?? 'Invalid verification link' }}</h1>
            <p class="subtitle" style="margin-top:0;">{{ $message ?? 'This verification link is not valid.' }}</p>
        </div>
        <div class="notice danger">
            {{ $message ?? 'This verification link is not valid.' }}
        </div>
        <p class="footer-note">
            Open a fresh verification session from the app instead of reusing an old link.
        </p>
    </section>
@endsection
