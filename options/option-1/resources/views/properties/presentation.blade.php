@php($details=$property->presentation??[])
@if($details)
<link rel="stylesheet" href="{{ asset('css/add-property.css') }}">
@if(!empty($details['label']) && $details['label']!=='None')<div class="panel"><span class="badge">{{ $details['label'] }}</span></div>@endif
@if(($details['tour_mode']??'none')!=='none')
<div class="panel"><h2>Virtual Tour 360</h2>
@if(($details['tour_mode']??'')==='embed' && !empty($details['tour_url']))
<iframe class="property-presentation-frame" src="{{ $details['tour_url'] }}" title="Virtual tour of {{ $property->title }}" loading="lazy" referrerpolicy="no-referrer" sandbox="allow-scripts allow-same-origin allow-presentation" allowfullscreen></iframe>
@elseif(($details['tour_mode']??'')==='image' && !empty($details['tour_image']))
<a href="{{ route('media.presentation',[$property,'tour']) }}" target="_blank" rel="noopener"><img class="floor-display-image" src="{{ route('media.presentation',[$property,'tour']) }}" alt="Panoramic view of {{ $property->title }}" loading="lazy"></a><p class="muted small">Open the panoramic image to explore the full view.</p>
@endif</div>
@endif
@if(!empty($details['video_url']))<div class="panel"><h2>Property video</h2><iframe class="property-presentation-frame" src="{{ $details['video_url'] }}" title="Video walkthrough of {{ $property->title }}" loading="lazy" referrerpolicy="no-referrer" sandbox="allow-scripts allow-same-origin allow-presentation" allowfullscreen></iframe></div>@endif
@if(!empty($details['floors_enabled']) && !empty($details['floors']))
<div class="panel"><h2>Floor plans</h2>
@foreach($details['floors'] as $index=>$floor)
<details class="review" @if($loop->first) open @endif><summary><strong>{{ $floor['name'] }}</strong></summary>
<div class="floor-details">
@if(isset($floor['price']))<span>₹{{ number_format((float)$floor['price']) }} {{ $floor['price_postfix']??'' }}</span>@endif
@if(isset($floor['size']))<span>{{ number_format((float)$floor['size']) }} {{ $floor['size_postfix']??'sqft' }}</span>@endif
@if(isset($floor['bedrooms']))<span>{{ $floor['bedrooms'] }} bedrooms</span>@endif
@if(isset($floor['bathrooms']))<span>{{ $floor['bathrooms'] }} bathrooms</span>@endif</div>
@if(!empty($floor['image']))<img class="floor-display-image" src="{{ route('media.presentation',[$property,'floor',$index]) }}" alt="{{ $floor['name'] }} floor plan" loading="lazy">@endif
@if(!empty($floor['description']))<p class="preserve-lines">{{ $floor['description'] }}</p>@endif
</details>
@endforeach</div>
@endif
@if(($details['contact_mode']??'account')==='other')
<div class="panel"><h2>Additional property contact</h2><h3>{{ $details['contact_name']??'' }}</h3>
@if(!empty($details['contact_email']))<p><a href="mailto:{{ $details['contact_email'] }}">{{ $details['contact_email'] }}</a></p>@endif
@if(!empty($details['contact_phone']))<p>{{ $details['contact_phone'] }}</p>@endif
<p class="muted small">Private dashboard inquiries are delivered to the listing owner.</p></div>
@endif
@endif
