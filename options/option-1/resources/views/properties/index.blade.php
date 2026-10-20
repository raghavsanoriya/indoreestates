@extends('layouts.app')
@section('content')
<section class="page-intro"><div class="container-wide"><p class="eyebrow green">FIND YOUR PLACE</p><h1>Explore properties</h1><p class="muted">Homes, spaces, and new beginnings.</p></div></section>
<div class="container-wide section listing-layout">
<aside class="filter-panel"><h3>Find your match</h3><form method="get" action="{{ route('properties.index') }}" class="stack-form">
<label>Keyword<input name="keyword" value="{{ request('keyword') }}" placeholder="Title, address or description"></label>
<label>Location<input name="location" value="{{ request('location') }}" placeholder="City or neighborhood"></label>
<div class="field-grid"><label>Purpose<select name="purpose"><option value="">Buy or rent</option>@foreach(['sale'=>'For sale','rent'=>'For rent'] as $v=>$label)<option value="{{ $v }}" @selected(request('purpose')===$v)>{{ $label }}</option>@endforeach</select></label>
<label>Type<select name="type"><option value="">All types</option>@foreach(['Apartment','Villa','House','Studio','Office','Land'] as $type)<option @selected(request('type')===$type)>{{ $type }}</option>@endforeach</select></label></div>
<div class="field-grid">@foreach(['min_price'=>'Min price (₹)','max_price'=>'Max price (₹)','bedrooms'=>'Min bedrooms','bathrooms'=>'Min bathrooms','min_area'=>'Min area (sqft)','max_area'=>'Max area (sqft)'] as $field=>$label)<label>{{ $label }}<input type="number" min="0" name="{{ $field }}" value="{{ request($field) }}"></label>@endforeach</div>
<details><summary>Amenities</summary><div class="checks">@foreach($amenities as $amenity)<label><input type="checkbox" name="amenities[]" value="{{ $amenity->id }}" @checked(in_array($amenity->id,request('amenities',[])))>{{ $amenity->name }}</label>@endforeach</div></details>
<label>Sort by<select name="sort">@foreach(['newest'=>'Newest first','price_asc'=>'Price: low to high','price_desc'=>'Price: high to low'] as $v=>$label)<option value="{{ $v }}" @selected(request('sort','newest')===$v)>{{ $label }}</option>@endforeach</select></label>
<input type="hidden" name="view" value="{{ request('view','grid') }}"><button class="button">Search properties</button><a href="{{ route('properties.index') }}">Clear filters</a>
</form></aside>
<div><div class="results-heading"><h3>{{ $properties->total() }} properties found</h3><div class="view-switch">@foreach(['grid'=>'Grid','list'=>'List','map'=>'Map'] as $v=>$label)<a @class(['active'=>request('view','grid')===$v]) href="{{ route('properties.index',array_merge(request()->except('page'),['view'=>$v])) }}">{{ $label }}</a>@endforeach</div></div>
@if(request('view')==='map')
<div class="panel"><h3>Property locations</h3><p>Choose a location to open its map. The results below match your search.</p><div class="map-links">@forelse($properties as $property)<a target="_blank" rel="noopener" href="https://www.openstreetmap.org/search?query={{ rawurlencode($property->address.' '.$property->location) }}">⌖ {{ $property->title }} · {{ $property->location }} ↗</a>@empty<p>No map locations match these filters.</p>@endforelse</div></div>
@endif
<div @class(['property-grid','two-columns','list-view'=>request('view')==='list'])>@forelse($properties as $property)<x-property-card :property="$property"/>@empty<div class="empty-state"><h3>No matching properties</h3><p>Try another location or clear some filters.</p></div>@endforelse</div>
<div class="pagination-wrap">{{ $properties->links() }}</div>
</div></div>
@endsection
