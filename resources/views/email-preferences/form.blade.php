@extends('layouts.verification')

@php
    $pageTitle = 'Email Preferences';
@endphp

@section('content')
    <section class="hero">
        <p class="eyebrow">Email Preferences</p>
        <h1>Choose which Luki emails you want to receive.</h1>
        <p class="subtitle">
            Authentication emails stay enabled for account security. This signed link expires on
            {{ $expiresAt?->timezone(config('app.timezone'))->format('D, d M Y \a\t H:i') }}.
        </p>
    </section>

    <section class="card stack">
        <form class="stack" method="POST" action="{{ $submitUrl }}">
            @csrf

            <label class="field-card" style="display:flex; align-items:flex-start; gap:12px;">
                <input type="checkbox" name="marketing_emails_enabled" value="1" {{ $preference->marketing_emails_enabled ? 'checked' : '' }} style="width:auto; margin-top:4px;">
                <span>
                    <strong>Promotional and marketing emails</strong>
                    <span style="display:block; color: var(--text-muted);">Special offers, product updates, and promotional campaigns.</span>
                </span>
            </label>

            <label class="field-card" style="display:flex; align-items:flex-start; gap:12px;">
                <input type="checkbox" name="booking_emails_enabled" value="1" {{ $preference->booking_emails_enabled ? 'checked' : '' }} style="width:auto; margin-top:4px;">
                <span>
                    <strong>Booking emails</strong>
                    <span style="display:block; color: var(--text-muted);">Booking summaries and other order-related emails.</span>
                </span>
            </label>

            <label class="field-card" style="display:flex; align-items:flex-start; gap:12px; opacity:.8;">
                <input type="checkbox" checked disabled style="width:auto; margin-top:4px;">
                <span>
                    <strong>Authentication emails</strong>
                    <span style="display:block; color: var(--text-muted);">Verification codes and security messages stay on.</span>
                </span>
            </label>

            <div class="button-row">
                <button class="btn" type="submit">Save Preferences</button>
            </div>
        </form>
    </section>
@endsection
