@extends('layouts.verification')

@php
    $pageTitle = $title ?? 'Invalid link';
@endphp

@section('content')
    <section class="hero">
        <p class="eyebrow">Email Preferences</p>
        <h1>{{ $title ?? 'Invalid link' }}</h1>
        <p class="subtitle">{{ $message ?? 'This email preferences link is invalid or expired.' }}</p>
    </section>
@endsection
