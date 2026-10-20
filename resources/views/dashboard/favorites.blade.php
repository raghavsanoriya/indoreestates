@extends('layouts.dashboard')
@section('dashboard-content')
<h3 class="mb-4">My Favourites</h3><div class="row">@forelse($properties as $property)<div class="col-md-6 d-flex mb-4"><x-property-card :property="$property" :saved-property-ids="$savedPropertyIds ?? []"/></div>@empty<div class="col-12"><div class="card card-body text-center py-5"><h4>No saved properties yet</h4><p>Save a property from the website to find it here.</p><a href="{{ route('properties.index') }}" class="btn btn-primary align-self-center">Explore Properties</a></div></div>@endforelse</div>{{ $properties->links() }}
@endsection
