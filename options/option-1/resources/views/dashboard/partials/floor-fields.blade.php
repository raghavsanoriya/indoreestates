<fieldset class="floor-card" data-floor-index="{{ $index }}">
    <legend><span class="floor-label">{{ $floor['name']??'Floor plan' }}</span><button type="button" class="remove-floor" aria-label="Remove floor plan">Remove floor</button></legend>
    @if(!empty($floor['id']))<input type="hidden" name="presentation[floors][{{ $index }}][id]" value="{{ $floor['id'] }}">@endif
    <div class="property-fields">
        <label>Floor Name: *<input name="presentation[floors][{{ $index }}][name]" value="{{ $floor['name']??'' }}" maxlength="100" required></label>
        <label>Floor Price (₹):<input type="number" name="presentation[floors][{{ $index }}][price]" value="{{ $floor['price']??'' }}" min="0" step="0.01"></label>
        <label>Price Postfix:<input name="presentation[floors][{{ $index }}][price_postfix]" value="{{ $floor['price_postfix']??'' }}" maxlength="30" placeholder="e.g. per month"></label>
        <label>Floor Size:<input type="number" name="presentation[floors][{{ $index }}][size]" value="{{ $floor['size']??'' }}" min="0" step="0.01"></label>
        <label>Size Postfix:<input name="presentation[floors][{{ $index }}][size_postfix]" value="{{ $floor['size_postfix']??'sqft' }}" maxlength="30"></label>
        <label>Bedrooms:<input type="number" name="presentation[floors][{{ $index }}][bedrooms]" value="{{ $floor['bedrooms']??'' }}" min="0" max="100" step="1"></label>
        <label>Bathrooms:<input type="number" name="presentation[floors][{{ $index }}][bathrooms]" value="{{ $floor['bathrooms']??'' }}" min="0" max="100" step="1"></label>
        <label class="span-two">Floor Image:<input type="file" name="presentation_files[floors][{{ $index }}]" accept="image/jpeg,image/png,image/webp"></label>
        @if($index!=='__INDEX__' && !empty($floor['image']) && $property->exists)<img class="presentation-image-thumb" src="{{ route('media.presentation',[$property,'floor',$index]) }}" alt="Current floor plan image">@endif
        <label class="span-all">Floor Description:<textarea name="presentation[floors][{{ $index }}][description]" maxlength="3000" rows="3">{{ $floor['description']??'' }}</textarea></label>
    </div>
</fieldset>
