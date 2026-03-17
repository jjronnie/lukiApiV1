@extends('layouts.admin')

@php
    $currentStatus = $verification->status->value ?? $verification->status;
    $selectedStatus = old('status', in_array($currentStatus, ['approved', 'rejected'], true) ? $currentStatus : 'approved');
@endphp

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#8b7d72]">Customer Verification</p>
                <h1 class="mt-1 text-2xl font-display">Review {{ $verification->user?->name ?? 'customer' }}</h1>
            </div>
            <a href="{{ route('admin.user-identity-verifications.index') }}" class="btn btn-light">Back to list</a>
        </div>

        <div class="grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
            <section class="admin-card space-y-4">
                <div>
                    <p class="admin-card-label">Submitted by</p>
                    <h2 class="mt-2 text-xl font-semibold text-[#1f2937]">{{ $verification->user?->name ?? 'Unknown customer' }}</h2>
                    <p class="mt-1 text-sm text-[#6b7280]">{{ $verification->user?->email ?? 'No email' }}</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                        <p class="admin-card-label">Status</p>
                        <p class="mt-2 text-base font-semibold capitalize text-[#1f2937]">{{ str_replace('_', ' ', $currentStatus) }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                        <p class="admin-card-label">ID Type</p>
                        <p class="mt-2 text-base font-semibold capitalize text-[#1f2937]">{{ str_replace('_', ' ', $verification->id_type) }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                        <p class="admin-card-label">Submitted</p>
                        <p class="mt-2 text-base font-semibold text-[#1f2937]">{{ $verification->submitted_at?->format('d M Y H:i') ?? '—' }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                        <p class="admin-card-label">Reviewed</p>
                        <p class="mt-2 text-base font-semibold text-[#1f2937]">{{ $verification->reviewed_at?->format('d M Y H:i') ?? '—' }}</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                    <p class="admin-card-label">Admin identity details</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.1em] text-[#8b7d72]">ID Number</p>
                            <p class="mt-1 text-sm text-[#1f2937]">{{ $verification->id_number ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.1em] text-[#8b7d72]">Date of Birth</p>
                            <p class="mt-1 text-sm text-[#1f2937]">{{ $verification->date_of_birth?->format('d M Y') ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.1em] text-[#8b7d72]">District</p>
                            <p class="mt-1 text-sm text-[#1f2937]">{{ $verification->district_name ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.1em] text-[#8b7d72]">County</p>
                            <p class="mt-1 text-sm text-[#1f2937]">{{ $verification->county_name ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.1em] text-[#8b7d72]">Sub-county</p>
                            <p class="mt-1 text-sm text-[#1f2937]">{{ $verification->sub_county_name ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.1em] text-[#8b7d72]">Parish</p>
                            <p class="mt-1 text-sm text-[#1f2937]">{{ $verification->parish_name ?: '—' }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.1em] text-[#8b7d72]">Village</p>
                            <p class="mt-1 text-sm text-[#1f2937]">{{ $verification->village_name ?: '—' }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="admin-card">
                <div class="mb-5">
                    <p class="admin-card-label">Review decision</p>
                    <h2 class="mt-2 text-xl font-semibold text-[#1f2937]">Approve or reject submission</h2>
                </div>

                <form
                    method="POST"
                    action="{{ route('admin.user-identity-verifications.review', $verification) }}"
                    class="space-y-4"
                    data-ug-locale-form
                    data-initial-district="{{ old('district_id', $verification->district_id) }}"
                    data-initial-county="{{ old('county_id', $verification->county_id) }}"
                    data-initial-sub-county="{{ old('sub_county_id', $verification->sub_county_id) }}"
                    data-initial-parish="{{ old('parish_id', $verification->parish_id) }}"
                    data-initial-village="{{ old('village_id', $verification->village_id) }}"
                >
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="approved" {{ $selectedStatus === 'approved' ? 'selected' : '' }}>Approve</option>
                                <option value="rejected" {{ $selectedStatus === 'rejected' ? 'selected' : '' }}>Reject</option>
                            </select>
                        </div>
                        <div>
                            <label for="id_number">ID number</label>
                            <input id="id_number" type="text" name="id_number" value="{{ old('id_number', $verification->id_number) }}" placeholder="Enter ID number">
                        </div>
                        <div>
                            <label for="date_of_birth">Date of birth</label>
                            <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($verification->date_of_birth)->format('Y-m-d')) }}">
                        </div>
                        <div>
                            <label for="district_id">District</label>
                            <select id="district_id" name="district_id" data-ug-district></select>
                            <input type="hidden" name="district_name" value="{{ old('district_name', $verification->district_name) }}">
                        </div>
                        <div>
                            <label for="county_id">County</label>
                            <select id="county_id" name="county_id" data-ug-county></select>
                            <input type="hidden" name="county_name" value="{{ old('county_name', $verification->county_name) }}">
                        </div>
                        <div>
                            <label for="sub_county_id">Sub-county</label>
                            <select id="sub_county_id" name="sub_county_id" data-ug-sub-county></select>
                            <input type="hidden" name="sub_county_name" value="{{ old('sub_county_name', $verification->sub_county_name) }}">
                        </div>
                        <div>
                            <label for="parish_id">Parish</label>
                            <select id="parish_id" name="parish_id" data-ug-parish></select>
                            <input type="hidden" name="parish_name" value="{{ old('parish_name', $verification->parish_name) }}">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="village_id">Village</label>
                            <select id="village_id" name="village_id" data-ug-village></select>
                            <input type="hidden" name="village_name" value="{{ old('village_name', $verification->village_name) }}">
                        </div>
                    </div>

                    <div>
                        <label for="rejection_reason">Rejection reason</label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="4" placeholder="Add feedback if you are rejecting this submission.">{{ old('rejection_reason', $verification->rejection_reason) }}</textarea>
                    </div>

                    <button type="submit" class="btn w-full">Save review</button>
                </form>
            </section>
        </div>

        <section class="admin-card">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="admin-card-label">Verification media</p>
                    <h2 class="mt-2 text-xl font-semibold text-[#1f2937]">Submitted images</h2>
                </div>
                @if($verification->canDeleteIdImages())
                    <p class="text-sm text-[#6b7280]">Front and back images can now be deleted if no longer needed.</p>
                @endif
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                @foreach (['selfie' => 'Selfie', 'id_front' => 'ID Front', 'id_back' => 'ID Back'] as $collection => $label)
                    @php($media = $verification->getFirstMedia($collection))
                    <article class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h3 class="text-sm font-semibold text-[#1f2937]">{{ $label }}</h3>
                            @if($media)
                                <a
                                    href="{{ route('admin.user-identity-verifications.media', [$verification, $collection]) }}"
                                    target="_blank"
                                    class="text-xs font-semibold uppercase tracking-[0.08em] text-[#d76a08]"
                                >
                                    Open
                                </a>
                            @endif
                        </div>

                        @if($media)
                            <a href="{{ route('admin.user-identity-verifications.media', [$verification, $collection]) }}" target="_blank">
                                <img
                                    src="{{ route('admin.user-identity-verifications.media', [$verification, $collection]) }}"
                                    alt="{{ $label }}"
                                    class="h-64 w-full rounded-2xl bg-[#111827] object-contain p-3"
                                >
                            </a>
                        @else
                            <div class="grid h-64 place-items-center rounded-2xl border border-dashed border-[#d8cec2] bg-white text-sm text-[#8b7d72]">
                                Image not available
                            </div>
                        @endif

                        <div class="mt-3 flex flex-wrap gap-2">
                            @if($media)
                                <a class="btn btn-light" href="{{ route('admin.user-identity-verifications.media', [$verification, $collection]) }}" target="_blank">Open full image</a>
                            @endif
                            @if($media && in_array($collection, ['id_front', 'id_back'], true) && $verification->canDeleteIdImages())
                                <form method="POST" action="{{ route('admin.user-identity-verifications.media.destroy', [$verification, $collection]) }}" onsubmit="return confirm('Delete this image from the verification record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light">Delete image</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
@endsection
