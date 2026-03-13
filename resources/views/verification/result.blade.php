@extends('layouts.verification')

@php
    $pageTitle = $title ?? 'Verification update';
    $toneClass = match (($tone ?? 'success')) {
        'warning' => 'warning',
        'danger' => 'danger',
        default => 'success',
    };
@endphp

@section('content')
    <section class="hero">
        <p class="eyebrow">Identity Verification</p>
        <h1>{{ $title ?? 'Verification update' }}</h1>
        <p class="subtitle">{{ $message ?? 'Return to the app to continue.' }}</p>
    </section>

    <section class="card stack">
        <div class="notice {{ $toneClass }}">
            {{ $message ?? 'Return to the app to continue.' }}
        </div>

        <div class="button-row">
            <button
                class="btn"
                type="button"
                onclick="window.close(); setTimeout(() => { if (!window.closed) history.go(-1); }, 180);"
            >
                Close Page
            </button>
        </div>

        <p class="footer-note">
            If this page does not close automatically, switch back to the app and pull to refresh your verification status.
        </p>
    </section>
@endsection
