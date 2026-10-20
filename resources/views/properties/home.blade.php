@extends('layouts.app')
@section('body-class','homez-home')
@section('content')
<div class="elementor elementor-19">
@include('partials.homez-hero')
@include('partials.homez-locations')
@include('partials.homez-listings')
@include('partials.homez-services')
@include('partials.homez-counts')
@include('partials.homez-types')
@if($reviews->isNotEmpty())<section class="o3-review-section container"><h2>Experiences with indoreestate</h2><div class="row">@foreach($reviews as $review)<div class="col-md-4"><article class="card card-body"><p>{{ $review->body }}</p><h6>{{ $review->author->name }}</h6><p class="text-warning">{{ str_repeat('★',$review->rating) }}</p></article></div>@endforeach</div></section>@endif
@include('partials.homez-cta')
</div>
@endsection
