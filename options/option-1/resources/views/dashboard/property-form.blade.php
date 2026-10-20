@extends('layouts.property-dashboard')
@section('content')
<div class="add-property-heading">
    <div><p class="eyebrow green">DASHBOARD / MY PROPERTIES</p><h1>{{ $property->exists ? 'Edit Property' : 'Add Property' }}</h1><p class="muted">Complete your listing details below. Save a private draft or submit it for approval.</p></div>
    <a class="button outline" href="{{ route('listings.index') }}">← My properties</a>
</div>
@include('properties.import-source', ['property'=>$property])
<p class="notice">Only Indore properties can be submitted or published. Imported project prices and unit measurements may be left blank when the developer has not supplied them.</p>
<form id="add-property-form" class="add-property-form" action="{{ $property->exists ? route('listings.update',$property) : route('listings.store') }}" method="post" enctype="multipart/form-data">
@csrf
@if($property->exists)@method('PUT')@endif
<nav class="property-section-nav" aria-label="Property form sections">
    <a href="#upload-media">Media</a><a href="#information">Information</a><a href="#pricing">Price</a><a href="#additional-information">Details</a><a href="#amenities">Amenities</a><a href="#virtual-tour">Virtual tour</a><a href="#videos">Videos</a><a href="#floors">Floors</a><a href="#agent-information">Agent</a>
</nav>

<section id="upload-media" class="panel property-form-panel">
    <div class="form-section-heading"><span class="section-number">01</span><div><h2>Upload Media</h2><p class="muted small">Show buyers and tenants what makes your property special.</p></div></div>
    @if($property->exists && $property->photos->isNotEmpty())
    <div class="photo-editor existing-property-photos">
        @foreach($property->photos as $photo)
        <div class="photo-tile">
            <img src="{{ $photo->url() }}" alt="Property photo {{ $loop->iteration }}">
            <input type="hidden" name="photo_order[]" value="{{ $photo->id }}">
            <label><input type="radio" name="cover_photo" value="{{ $photo->id }}" @checked(old('cover_photo',$property->photos->first()?->id)==$photo->id)>Cover photo</label>
            <label><input type="checkbox" name="remove_photos[]" value="{{ $photo->id }}" @checked(in_array($photo->id,old('remove_photos',[])))>Remove photo</label>
            <div class="photo-tools"><button type="button" data-move="up" aria-label="Move photo {{ $loop->iteration }} earlier">←</button><button type="button" data-move="down" aria-label="Move photo {{ $loop->iteration }} later">→</button></div>
        </div>
        @endforeach
    </div>
    @endif
    <div class="media-dropzone" id="property-photo-dropzone">
        <span class="upload-symbol" aria-hidden="true">⇧</span>
        <label for="photo-uploads" class="button">♧ &nbsp; Select photos</label>
        <p class="upload-hint">or drag photos here<br>(Up to 20 photos)</p>
        <input id="photo-uploads" type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple aria-label="Property photos">
    </div>
    <p id="photo-feedback" role="status" class="muted small"></p>
    <div id="upload-preview" class="photo-editor"></div>
    @error('photos')<p class="field-error">{{ $message }}</p>@enderror
    <p class="muted small photo-order-help">JPEG, PNG or WebP, up to 5 MB each. Select a cover photo and use the arrows to reorder existing photos. At least one photo is required before submitting.</p>
</section>

<section id="information" class="panel property-form-panel">
    <div class="form-section-heading"><span class="section-number">02</span><h2>Information</h2></div>
    <div class="property-fields information-fields">
        <label class="span-all">Title: *<input name="title" value="{{ old('title',$property->title) }}" placeholder="Enter a descriptive property title" required maxlength="200"></label>
        <label class="span-all">Description: *<textarea name="description" maxlength="20000" rows="5" placeholder="Describe your property, its surroundings and standout features" required>{{ old('description',$property->description) }}</textarea></label>
        <label>Full Address: *<input name="address" value="{{ old('address',$property->address) }}" placeholder="Enter property full address" required maxlength="255"></label>
        <label>Zip Code:<input name="zip_code" value="{{ old('zip_code',$property->zip_code) }}" placeholder="Postal code" maxlength="20"></label>
        <label>Country: *<select name="country" required autocomplete="country-name">@foreach(array_unique([old('country',$property->country??'India'),'India','United States','United Kingdom','Russia']) as $country)<option value="{{ $country }}" @selected(old('country',$property->country??'India')===$country)>{{ $country }}</option>@endforeach</select></label>
        <label class="half-width">Province / State:<input name="state" value="{{ old('state',$property->state) }}" placeholder="None" maxlength="100" list="property-states"><datalist id="property-states">@foreach(['Madhya Pradesh','Maharashtra','Karnataka','Tamil Nadu','Telangana','Delhi','Gujarat','Rajasthan','Uttar Pradesh','West Bengal','Kerala'] as $state)<option value="{{ $state }}">@endforeach</datalist></label>
        <label class="half-width">Neighborhood:<input name="neighborhood" value="{{ old('neighborhood',$property->neighborhood) }}" placeholder="None" maxlength="100"></label>
        <label class="span-all">Location / City: *<input name="location" value="{{ old('location',$property->location??'Indore') }}" placeholder="City or location" required maxlength="100"></label>
        <div class="location-map span-all"><iframe id="property-location-map" src="https://maps.google.com/maps?q={{ rawurlencode(old('location',$property->location??'Indore').' '.old('country',$property->country??'India')) }}&amp;z=12&amp;output=embed" title="Property location map" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
        <details class="map-coordinates span-all"><summary>Set exact map coordinates (optional)</summary><div class="property-fields"><label>Latitude:<input type="number" name="latitude" step="0.0000001" min="-90" max="90" value="{{ old('latitude',$property->latitude) }}" placeholder="Optional"></label><label>Longitude:<input type="number" name="longitude" step="0.0000001" min="-180" max="180" value="{{ old('longitude',$property->longitude) }}" placeholder="Optional"></label><button type="button" class="button outline" id="update-location-map">Update map</button></div></details>
    </div>
</section>

<section id="pricing" class="panel property-form-panel">
    <div class="form-section-heading"><span class="section-number">03</span><h2>Price</h2></div>
    <div class="property-fields price-fields">
        <label>Price (₹):<input type="number" name="price" step="0.01" min="0" max="999999999999.99" value="{{ old('price',$property->price) }}" placeholder="{{ $property->source_key ? 'Leave blank for Contact us' : 'Example: 7500000' }}" @required(!$property->source_key)></label>
        <label>Unit Price:<select name="presentation[unit_price]"><option value="total" @selected(old('presentation.unit_price',data_get($property->presentation,'unit_price','total'))==='total')>Total price</option><option value="per_sqft" @selected(old('presentation.unit_price',data_get($property->presentation,'unit_price'))==='per_sqft')>Per square foot</option></select></label>
        <label class="rental-period">Rental period:<select name="rental_period">@foreach(['month','week','year'] as $period)<option value="{{ $period }}" @selected(old('rental_period',$property->rental_period??'month')===$period)>{{ ucfirst($period) }}</option>@endforeach</select></label>
        <label>Before Price Label:<input name="presentation[before_price_label]" value="{{ old('presentation.before_price_label',data_get($property->presentation,'before_price_label')) }}" placeholder="Example: Starting from" maxlength="50"></label>
        <label>After Price Label:<input name="presentation[after_price_label]" value="{{ old('presentation.after_price_label',data_get($property->presentation,'after_price_label')) }}" placeholder="Example: Negotiable" maxlength="50"></label>
    </div>
    <p class="muted small">All prices are in Indian rupees. The rental period applies to rental listings only.</p>
</section>

<section id="additional-information" class="panel property-form-panel">
    <div class="form-section-heading"><span class="section-number">04</span><h2>Additional Information</h2></div>
    <div class="property-fields">
        <label>Property Type: *<select name="type" required>@foreach(['Apartment','Villa','House','Studio','Office','Land'] as $type)<option value="{{ $type }}" @selected(old('type',$property->type??'Apartment')===$type)>{{ $type }}</option>@endforeach</select></label>
        <label>Property Status: *<select name="purpose"><option value="sale" @selected(old('purpose',$property->purpose??'sale')==='sale')>For sale</option><option value="rent" @selected(old('purpose',$property->purpose)==='rent')>For rent</option></select></label>
        <label>Property Label:<select name="presentation[label]">@foreach(['None','Featured','New listing','Hot offer'] as $label)<option @selected(old('presentation.label',data_get($property->presentation,'label','None'))===$label)>{{ $label }}</option>@endforeach</select></label>
        <label>Size (SqFt):<input type="number" name="area" step="0.01" min="1" value="{{ old('area',$property->area) }}" @required(!$property->source_key)></label>
        <label>Land Area (SqFt):<input type="number" name="land_area" step="0.01" min="0" value="{{ old('land_area',$property->land_area) }}"></label>
        <label>Property ID:<input value="{{ $property->reference??'Automatically generated after saving' }}" readonly aria-readonly="true"></label>
        @foreach(['rooms'=>'Rooms','bedrooms'=>'Bedrooms','bathrooms'=>'Bathrooms','garages'=>'Garages'] as $field=>$label)
        <label>{{ $label }}:<input type="number" name="{{ $field }}" min="0" max="100" step="1" value="{{ old($field,$property->$field??($property->source_key ? '' : 0)) }}" @required(!$property->source_key)></label>
        @endforeach
        <label>Garage Size (SqFt):<input type="number" name="garage_area" step="0.01" min="0" value="{{ old('garage_area',$property->garage_area) }}"></label>
        <label>Year Built:<input type="number" name="year_built" min="1800" max="{{ now()->year+10 }}" value="{{ old('year_built',$property->year_built) }}"></label>
        <label>Availability:<select name="availability">@foreach(['available','sold','rented'] as $availability)<option @selected(old('availability',$property->availability??'available')===$availability)>{{ $availability }}</option>@endforeach</select></label>
    </div>
</section>

<section id="amenities" class="panel property-form-panel">
    <div class="form-section-heading"><span class="section-number">05</span><h2>Amenities</h2></div>
    @php
        $amenityGroups=['Home safety:'=>['Smoke alarm','Self check-in with lockbox','Carbon monoxide alarm','Security cameras'],'Bedroom'=>['Hangers','Extra pillows & blankets','Bed linens','TV with standard cable'],'Kitchen:'=>['Refrigerator','Dishwasher','Microwave','Coffee maker']];
        $groupedNames=collect($amenityGroups)->flatten()->all();
    @endphp
    <div class="reference-amenity-groups">
        @foreach($amenityGroups as $group=>$names)<div><h3>{{ $group }}</h3><div class="checks">
            @foreach($names as $name)@php($amenity=$amenities->firstWhere('name',$name))@if($amenity)<label><input type="checkbox" name="amenities[]" value="{{ $amenity->id }}" @checked(in_array($amenity->id,old('amenities',$property->exists?$property->amenities->pluck('id')->all():[])))>{{ $amenity->name }}</label>@endif @endforeach
        </div></div>@endforeach
    </div>
    @if($amenities->whereNotIn('name',$groupedNames)->isNotEmpty())<details class="extra-amenities"><summary>More property features</summary><div class="checks amenity-checkbox-grid">@foreach($amenities->whereNotIn('name',$groupedNames) as $amenity)<label><input type="checkbox" name="amenities[]" value="{{ $amenity->id }}" @checked(in_array($amenity->id,old('amenities',$property->exists?$property->amenities->pluck('id')->all():[])))>{{ $amenity->name }}</label>@endforeach</div></details>@endif
</section>

<section id="virtual-tour" class="panel property-form-panel">
    <div class="form-section-heading"><span class="section-number">06</span><h2>Virtual Tour 360</h2></div>
    <p class="reference-control-label">Virtual Tour Type:</p>
    <input type="hidden" name="presentation[tour_mode]" value="none">
    <div class="reference-radios" id="tour-mode"><label><input type="radio" name="presentation[tour_mode]" value="embed" @checked(old('presentation.tour_mode',data_get($property->presentation,'tour_mode'))==='embed')>Embedded code</label><label><input type="radio" name="presentation[tour_mode]" value="image" @checked(old('presentation.tour_mode',data_get($property->presentation,'tour_mode'))==='image')>Upload image</label><button type="button" class="clear-tour">Clear tour</button></div>
    <div data-tour-mode="embed"><label>Embedded Code Virtual 360<textarea name="presentation[tour_url]" rows="3" maxlength="2000" placeholder="Paste a supported HTTPS tour URL or iframe embed">{{ old('presentation.tour_url',data_get($property->presentation,'tour_url')) }}</textarea></label><p class="muted small">Matterport, Kuula, Panoee, Giraffe360 and Google Maps embeds are supported. Only the validated URL is saved.</p></div>
    <div data-tour-mode="image"><label>Panoramic image<input type="file" name="presentation_files[tour]" accept="image/jpeg,image/png,image/webp"></label><p class="muted small">JPEG, PNG or WebP, up to 5 MB. Use a wide panoramic image.</p>@if(data_get($property->presentation,'tour_image'))<img class="presentation-image-thumb" src="{{ route('media.presentation',[$property,'tour']) }}" alt="Current panoramic image">@endif</div>
</section>

<section id="videos" class="panel property-form-panel">
    <div class="form-section-heading"><span class="section-number">07</span><h2>Videos</h2></div>
    <label>Video URL:<input type="url" name="presentation[video_url]" value="{{ old('presentation.video_url',data_get($property->presentation,'video_url')) }}" placeholder="https://www.youtube.com/watch?v=… or https://vimeo.com/…" maxlength="500"></label>
    <p class="muted small">Add an optional YouTube or Vimeo property walkthrough.</p>
</section>

<section id="floors" class="panel property-form-panel">
    <div class="form-section-heading"><span class="section-number">08</span><h2>Floors</h2></div>
    <p class="reference-control-label">Enable Floor Plan:</p>
    <div class="reference-radios floor-toggle"><label><input id="floors-enabled" type="radio" name="presentation[floors_enabled]" value="1" @checked(old('presentation.floors_enabled',data_get($property->presentation,'floors_enabled',false)))>Enable</label><label><input id="floors-disabled" type="radio" name="presentation[floors_enabled]" value="0" @checked(!old('presentation.floors_enabled',data_get($property->presentation,'floors_enabled',false)))>Disable</label></div>
    <div id="floor-editor">
        <div id="floor-list">
            @foreach(old('presentation.floors',data_get($property->presentation,'floors',[]))??[] as $index=>$floor)
                @include('dashboard.partials.floor-fields',['index'=>$index,'floor'=>$floor])
            @endforeach
        </div>
        <button type="button" class="button outline" id="add-floor">＋ Add floor</button><p class="muted small">Add up to 10 floor plans. Each floor image can be up to 5 MB.</p>
    </div>
    <template id="floor-template">@include('dashboard.partials.floor-fields',['index'=>'__INDEX__','floor'=>[]])</template>
</section>

<section id="agent-information" class="panel property-form-panel">
    <div class="form-section-heading"><span class="section-number">09</span><h2>Agent Information</h2></div>
    <p class="reference-control-label">Choose type agent information?</p>
    <div class="reference-radios" id="contact-mode"><label><input type="radio" name="presentation[contact_mode]" value="account" @checked(old('presentation.contact_mode',data_get($property->presentation,'contact_mode','account'))==='account')>Your current user information</label><label><input type="radio" name="presentation[contact_mode]" value="other" @checked(old('presentation.contact_mode',data_get($property->presentation,'contact_mode'))==='other')>Other contact</label></div>
    <div class="agent-account-summary"><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->email }}</span><p class="muted small">Dashboard inquiries are always delivered to the listing owner.</p></div>
    <div id="other-contact" class="property-fields">
        <label>Contact name:<input name="presentation[contact_name]" value="{{ old('presentation.contact_name',data_get($property->presentation,'contact_name')) }}" maxlength="120"></label>
        <label>Contact email:<input type="email" name="presentation[contact_email]" value="{{ old('presentation.contact_email',data_get($property->presentation,'contact_email')) }}" maxlength="255"></label>
        <label>Contact phone:<input name="presentation[contact_phone]" value="{{ old('presentation.contact_phone',data_get($property->presentation,'contact_phone')) }}" maxlength="30"></label>
    </div>
</section>

<div class="property-form-actions">
    <p class="muted small">Your listing will appear on the website after administrator approval.</p>
    <div class="form-actions"><button type="submit" class="button" name="intent" value="submit">{{ $property->exists ? 'Update Property' : 'Add Property' }}</button><button type="submit" class="button outline" name="intent" value="preview">Save & Preview</button><button type="submit" class="draft-text-button" name="intent" value="draft">Save private draft</button></div>
</div>
</form>
@endsection
