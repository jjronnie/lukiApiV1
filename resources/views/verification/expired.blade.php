@extends('layouts.verification')

@php
    $pageTitle = $title ?? 'Verification unavailable';
@endphp

@section('content')
    <section class="card stack">
        <div style="display:grid; gap:12px; text-align:center;">
            <p class="eyebrow" style="margin-bottom:0;">Verification</p>
            <h1>{{ $title ?? 'Verification unavailable' }}</h1>
            <p class="subtitle" style="margin-top:0;">{{ $message ?? 'This verification page is no longer available.' }}</p>
        </div>
        <div class="notice warning">
            {{ $message ?? 'This verification page is no longer available.' }}
        </div>
    </section>
@endsection
