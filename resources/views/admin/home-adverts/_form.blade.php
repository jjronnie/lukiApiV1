<label>Title</label>
<input name="title" value="{{ old('title', $advert?->title ?? '') }}" required>

<label>Headline</label>
<input name="headline" value="{{ old('headline', $advert?->headline ?? '') }}" placeholder="Optional short headline">

<label>Description</label>
<textarea name="description" rows="4" placeholder="Optional longer description">{{ old('description', $advert?->description ?? '') }}</textarea>

<label>Button Text</label>
<input name="button_text" value="{{ old('button_text', $advert?->button_text ?? '') }}" placeholder="Open, Learn more, Explore...">

<label>Link Type</label>
<select name="link_type">
    @php($selectedLinkType = old('link_type', $advert?->link_type ?? 'none'))
    <option value="none" {{ $selectedLinkType === 'none' ? 'selected' : '' }}>No link</option>
    <option value="internal" {{ $selectedLinkType === 'internal' ? 'selected' : '' }}>Internal app route</option>
    <option value="external" {{ $selectedLinkType === 'external' ? 'selected' : '' }}>External URL</option>
</select>

<label>Link Target</label>
<input name="link_target" value="{{ old('link_target', $advert?->link_target ?? '') }}" placeholder="/services or https://example.com">

<label>Image URL</label>
<input name="image_url" value="{{ old('image_url', $advert?->image_url ?? '') }}" placeholder="https://..." required>

<label>Sort Order</label>
<input type="number" name="sort_order" value="{{ old('sort_order', $advert?->sort_order ?? 0) }}">

<label>Starts At</label>
<input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($advert?->starts_at)->format('Y-m-d\\TH:i')) }}">

<label>Ends At</label>
<input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($advert?->ends_at)->format('Y-m-d\\TH:i')) }}">

<label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $advert?->is_active ?? true) ? 'checked' : '' }} style="width:auto;"> Active</label>
