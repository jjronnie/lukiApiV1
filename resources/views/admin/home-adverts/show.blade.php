<x-app-layout>
<h1>{{ $advert->title }}</h1>
<p>Headline: {{ $advert->headline ?: '-' }}</p>
<p>Description: {{ $advert->description ?: '-' }}</p>
<p>Button Text: {{ $advert->button_text ?: '-' }}</p>
<p>Link Type: {{ $advert->link_type }}</p>
<p>Link Target: {{ $advert->link_target ?: '-' }}</p>
<p>Image URL: <a href="{{ $advert->image_url }}" target="_blank" rel="noreferrer">{{ $advert->image_url }}</a></p>
<p>Active Window: {{ $advert->starts_at?->format('d M Y H:i') ?? 'Now' }} - {{ $advert->ends_at?->format('d M Y H:i') ?? 'Open' }}</p>
<p>Status: {{ $advert->is_active ? 'Active' : 'Inactive' }}</p>
<div class="actions">
    <a class="btn" href="{{ route('admin.home-adverts.edit', $advert) }}">Edit</a>
    <form class="inline" method="POST" action="{{ route('admin.home-adverts.destroy', $advert) }}" onsubmit="return confirm('Delete this advert?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-light">Delete</button>
    </form>
</div>
@if($advert->image_url)
    <div class="mt-4">
        <img src="{{ $advert->image_url }}" alt="{{ $advert->title }}" style="max-width: 100%; border-radius: 16px;">
    </div>
@endif
</x-app-layout>
