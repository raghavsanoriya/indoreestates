<div class="mobile-detail-summary">
    <span class="mobile-detail-handle" aria-hidden="true"></span>
    <div class="mobile-detail-eyebrow">{{ $property->type }} <span>·</span> For {{ ucfirst($property->purpose) }}</div>
    <h1>{{ $property->title }}</h1>
    <p class="mobile-detail-address"><span class="material-icons-outlined" aria-hidden="true">location_on</span>{{ $property->address }}, {{ $property->location }}</p>
    <div class="mobile-detail-price-row"><strong>{{ $property->priceLabel() }}</strong><span>{{ $property->reference }}</span></div>
    @if($property->reviews->isNotEmpty())<a class="mobile-detail-rating" href="#property-reviews">★ {{ number_format((float) $property->reviews->avg('rating'), 1) }} <span>{{ $property->reviews->count() }} approved reviews</span></a>@endif
    <div class="mobile-detail-facts">
        @if($property->bedrooms)<span><strong>{{ $property->bedrooms }}</strong> Bedrooms</span>@endif
        @if($property->bathrooms)<span><strong>{{ $property->bathrooms }}</strong> Bathrooms</span>@endif
        @if($property->area)<span><strong>{{ number_format($property->area) }}</strong> Sq. ft.</span>@endif
        @if(!$property->bedrooms && !$property->bathrooms && !$property->area)<span><strong>Indore</strong> Madhya Pradesh</span>@endif
    </div>
    <nav class="mobile-detail-tabs" aria-label="Property sections"><a href="#property-about">Overview</a><a href="#property-amenities">Amenities</a><a href="#property-location">Location</a><a href="#property-enquiry">Enquiry</a></nav>
</div>
@if(!$preview)
    <div class="mobile-detail-cta"><div><span>{{ $property->price === null ? 'INTERESTED IN THIS PROPERTY?' : 'PROPERTY PRICE' }}</span><strong>{{ $property->price === null ? 'Let’s find your next home.' : $property->priceLabel() }}</strong></div><a href="#property-enquiry">Enquire now <span aria-hidden="true">↗</span></a></div>
@endif
