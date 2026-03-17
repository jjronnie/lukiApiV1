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
    <section class="card stack">
        <div style="display:grid; place-items:center; gap:18px; padding:10px 6px 4px; text-align:center;">
            @if(($tone ?? 'success') === 'success')
                <div style="width:104px; height:104px; border-radius:999px; background:#edf8f1; display:grid; place-items:center;">
                    <svg width="54" height="54" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5"></path>
                    </svg>
                </div>
            @endif

            <div>
                <h1 style="margin:0 0 12px;">{{ $title ?? 'Verification update' }}</h1>
                <p class="subtitle" style="margin:0;">{{ $message ?? 'Return to the app to continue.' }}</p>
            </div>
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
            If this page does not close automatically, return to the app and refresh your verification status.
        </p>
    </section>
@endsection
