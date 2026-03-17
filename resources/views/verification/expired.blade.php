@extends('layouts.verification')

@php
    $pageTitle = $title ?? 'Session expired';
@endphp

@section('content')
    <section class="card stack">
        <div style="display:grid; gap:12px; text-align:center;">
            <p class="eyebrow" style="margin-bottom:0;">Verification</p>
            <h1>{{ $title ?? 'Session expired' }}</h1>
            <p class="subtitle" style="margin-top:0;">{{ $message ?? 'This verification session has expired.' }}</p>
        </div>
        <div class="notice warning">
            {{ $message ?? 'This verification session has expired.' }}
        </div>
        <p class="footer-note">
            Return to the app and start a new verification session when you are ready.
        </p>
    </section>
@endsection
