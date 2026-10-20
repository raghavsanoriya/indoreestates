@props(['property'])
<article class="property-card">
    <a class="property-image" href="{{ route('properties.show',$property) }}"><img src="{{ $property->coverUrl() }}" alt="{{ $property->title }}" loading="lazy"><span class="badge">For {{ $property->purpose }}</span></a>
    <div class="property-content"><p class="muted small">{{ $property->type }} · {{ $property->location }}</p><h3><a href="{{ route('properties.show',$property) }}">{{ $property->title }}</a></h3><p class="address">{{ $property->address }}</p>
    @if($property->source_key)<p class="muted small">{{ $property->source_details['developer'] }} · Developer project</p>@endif
    <div class="property-features">@if($property->bedrooms !== null)<span>▱ {{ $property->bedrooms }} beds</span>@endif @if($property->bathrooms !== null)<span>◈ {{ $property->bathrooms }} baths</span>@endif @if($property->area !== null)<span>▣ {{ number_format((float)$property->area) }} sqft</span>@elseif($property->source_key)<span>{{ $property->source_details['unit_size'] ?? $property->source_details['project_type'] ?? 'Project details available' }}</span>@endif</div>
    <div class="card-bottom"><strong>{{ $property->priceLabel() }}</strong>@if($property->price === null)<a class="button small" href="{{ route('contact', ['property'=>$property->id]) }}">Contact us</a>@else<a href="{{ route('properties.show',$property) }}" aria-label="View {{ $property->title }}">View property ↗</a>@endif</div></div>
</article>
