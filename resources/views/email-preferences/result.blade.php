@extends('layouts.verification')

@php
    $pageTitle = $title ?? 'Preferences updated';
@endphp

@section('content')
    <section class="hero">
        <p class="eyebrow">Email Preferences</p>
        <h1>{{ $title ?? 'Preferences updated' }}</h1>
        <p class="subtitle">{{ $message ?? 'Your email preferences were updated successfully.' }}</p>
    </section>
@endsection
