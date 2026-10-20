@props(['property'])
<div class="homelengo-box">
    <div class="archive-top"><a class="images-group" href="{{ route('properties.show',$property) }}">
        <div class="images-style"><img src="{{ $property->coverUrl() }}" alt="{{ $property->title }}" width="615" height="405" loading="lazy"></div>
        <div class="top"><ul class="d-flex gap-6">@if($property->source_key)<li class="flag-tag primary">Developer project</li>@endif<li class="flag-tag style-1">For {{ ucfirst($property->purpose) }}</li></ul></div>
        <div class="bottom"><span class="icon icon-mapPin"></span>{{ $property->address }}</div>
    </a></div>
    <div class="archive-bottom">
        <div class="content-top"><h6 class="text-capitalize"><a class="link" href="{{ route('properties.show',$property) }}">{{ $property->title }}</a></h6>
            <ul class="meta-list">
                @if($property->bedrooms !== null)<li class="item"><i class="icon icon-bed"></i><span class="text-variant-1">Beds:</span><span class="fw-6">{{ $property->bedrooms }}</span></li>@endif
                @if($property->bathrooms !== null)<li class="item"><i class="icon icon-bath"></i><span class="text-variant-1">Baths:</span><span class="fw-6">{{ $property->bathrooms }}</span></li>@endif
                @if($property->area !== null)<li class="item"><i class="icon icon-sqft"></i><span class="text-variant-1">Sqft:</span><span class="fw-6">{{ number_format((float)$property->area) }}</span></li>
                @elseif($property->source_key)<li class="item">{{ $property->source_details['unit_size'] ?? $property->source_details['project_type'] ?? 'Project details available' }}</li>@endif
            </ul>
        </div>
        <div class="content-bottom">
            <div class="d-flex gap-8 align-items-center">
                @if($property->source_key)<span>{{ $property->source_details['developer'] }}</span>
                @else<div class="avatar avt-40 round"><img src="{{ $property->owner->avatar_path ? route('media.avatar',$property->owner) : asset('branding/avatar-placeholder.svg') }}" alt="" width="34" height="34"></div><span>{{ $property->owner->name }}</span>@endif
            </div>
            <div class="property-price-action"><h6 class="price">{{ $property->priceLabel() }}</h6>@if($property->price === null)<a class="tf-btn primary price-contact" href="{{ route('contact',['property'=>$property->id]) }}">Contact us</a>@endif</div>
        </div>
    </div>
</div>
