@extends('layouts.app')
@section('content')
<div class="page-wrapper"><x-breadcrumb :title="$title"/>
@if($page==='faq.html')@include('partials.dreams-faq')@else
<div class="content"><div class="container"><div class="row align-items-center">@if($page==='about-us.html')<div class="col-lg-6 mb-4"><img class="img-fluid rounded" src="{{ asset('assets/img/about-us/about-us-01.jpg') }}" alt=""></div>@endif<div class="{{ $page==='about-us.html'?'col-lg-6':'col-lg-10 mx-auto' }}"><div class="card"><div class="card-body">
@switch($page)
@case('about-us.html')<h2>Your Address in Indore</h2><p>indoreestate helps you explore properties and developer projects in Indore, Madhya Pradesh. Browse available listings, save favourites and send private enquiries through your account.</p><p>Imported projects identify their developer and source. Confirm current availability, specifications and pricing before making a decision.</p>@break
@case('our-service.html')<h2>Explore, Save and Enquire</h2><p>Search Indore properties by location, type, sale or rent, price and amenities. Save properties to your account and send private enquiries.</p><p>Agents can submit listings through the dashboard. Listings are reviewed by an administrator before publication.</p>@break
@case('pricing.html')<p>Online purchases and paid subscription plans are not available. Contact the team with your listing requirements.</p>@break
@case('privacy-policy.html')<p>Please contact the indoreestate team for the current privacy policy or questions about your account and submitted information.</p>@break
@default<h2>No articles published yet</h2><p>Browse our property listings for current project information.</p>
@endswitch
<div class="d-flex gap-2 flex-wrap mt-4"><a class="btn btn-primary" href="{{ route('properties.index') }}">Explore Properties</a><a class="btn btn-outline-primary" href="{{ route('contact') }}">Contact Us</a></div></div></div></div></div></div></div>@endif</div>
@endsection
