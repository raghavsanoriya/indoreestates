@extends('layouts.app')
@section('content')
<section class="flat-title-page"><div class="container"><h1>{{ $title }}</h1><ul class="breadcrumb"><li><a href="{{ route('home') }}">Home</a></li><li>{{ $title }}</li></ul></div></section>
<section class="flat-section"><div class="container"><div class="widget-box-2">
@switch($page)
    @case('about-us.html')
        <h2>Your address in Indore</h2>
        <p>indoreestate helps you explore properties and developer projects in Indore, Madhya Pradesh. Browse available listings, save favourites and send private enquiries through your account.</p>
        <p>Imported projects identify their developer and source. Please confirm current availability, specifications and pricing before making a decision.</p>
        @break
    @case('our-service.html')
        <h2>Explore, save and enquire</h2>
        <p>Search Indore properties by location, type, sale or rent, price and amenities. Save properties to your account and contact the listing team for more information.</p>
        <p>Agents can submit listings through the dashboard. Listings are reviewed by an administrator before publication.</p>
        @break
    @case('faq.html')
        <h2>Property questions</h2>
        <h3>Where are the properties located?</h3><p>Public listings on indoreestate are for Indore only.</p>
        <h3>Why do some listings say Price on request?</h3><p>The source has not provided a price. Use the Contact us button on that listing to request current pricing.</p>
        <h3>How can I save a property or send an enquiry?</h3><p>Sign in to save favourites, send private enquiries and view replies in your dashboard.</p>
        <h3>How do I publish a listing?</h3><p>Contact the administrator for agent access. Submit your property through the dashboard for review.</p>
        @break
    @case('pricing.html')
        <p>Online purchases and paid subscription plans are not available. Contact the team with your listing requirements.</p>
        @break
    @case('privacy-policy.html')
        <p>Please contact the indoreestate team for the current privacy policy or questions about your account and submitted information.</p>
        @break
    @default
        <div class="empty-state"><h2>No articles published yet</h2><p>Browse our property listings for current project information.</p></div>
@endswitch
<div class="mt-24"><a class="tf-btn primary" href="{{ route('properties.index') }}">Explore properties</a> <a class="tf-btn btn-line" href="{{ route('contact') }}">Contact us</a></div>
</div></div></section>
@endsection
