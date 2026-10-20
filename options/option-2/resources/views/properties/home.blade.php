@extends('layouts.app')
@section('content')
@include('partials.home-two-hero')
@include('partials.home-two-search')
@include('partials.home-two-about')
@include('partials.home-two-types')
@include('partials.home-two-support')
@include('partials.home-two-popular')
@include('partials.home-two-benefits')
@include('partials.home-two-locations')
@include('partials.home-two-work')
@if($reviews->isNotEmpty())<section class="customer-review-section"><div class="container"><div class="section-title-2"><h2>Customer <span>Reviews</span></h2><p>Experiences shared by our customers</p></div><div class="row">@foreach($reviews as $review)<div class="col-lg-4"><div class="card card-body"><p>{{ $review->body }}</p><h6>{{ $review->author->name }}</h6><p class="text-warning mb-0">{{ str_repeat('★',$review->rating) }}</p></div></div>@endforeach</div></div></section>@endif
@include('partials.home-two-faq')
@endsection
