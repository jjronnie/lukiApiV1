<label>Name</label><input name="name" value="{{ old('name', $zone?->name ?? '') }}" required>
<label>Slug</label><input name="slug" value="{{ old('slug', $zone?->slug ?? '') }}" required>
<label>Center Latitude</label><input name="center_lat" value="{{ old('center_lat', $zone?->center_lat ?? '') }}" placeholder="0.3476">
<label>Center Longitude</label><input name="center_lng" value="{{ old('center_lng', $zone?->center_lng ?? '') }}" placeholder="32.5825">
<label>Radius (KM)</label><input type="number" step="0.01" name="radius_km" value="{{ old('radius_km', $zone?->radius_km ?? '') }}" placeholder="12">
<label>Fee Amount (UGX)</label><input type="number" name="fee_amount" value="{{ old('fee_amount', $zone?->fee_amount ?? 0) }}" required>
<label>Sort Order</label><input type="number" name="sort_order" value="{{ old('sort_order', $zone?->sort_order ?? 0) }}">
<label><input type="checkbox" name="is_fallback" value="1" {{ old('is_fallback', $zone?->is_fallback ?? false) ? 'checked' : '' }} style="width:auto;"> Fallback zone for locations outside all defined radiuses</label>
<label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $zone?->is_active ?? true) ? 'checked' : '' }} style="width:auto;"> Active</label>
