<x-app-layout>
<div class="actions">
    <h1>Edit Home Advert</h1>
</div>
<form method="POST" action="{{ route('admin.home-adverts.update', $advert) }}">
    @csrf
    @method('PUT')
    @include('admin.home-adverts._form', ['advert' => $advert])
    <button type="submit">Save</button>
</form>
</x-app-layout>
