<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ request()->routeIs('listings.create') ? 'Add Property' : (request()->routeIs('listings.edit') ? 'Edit Property' : 'Dashboard') }} | indoreestate</title>
    <link rel="icon" href="{{ asset('branding/favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('template/_next/static/css/2ac4ac2d5afb7e7c.css') }}">
    <link rel="stylesheet" href="{{ asset('template/_next/static/css/98298e6c021b1ea1.css') }}">
    <link rel="stylesheet" href="{{ asset('css/estate.css') }}">
    <link rel="stylesheet" href="{{ asset('css/add-property.css') }}">
    <script src="{{ asset('js/estate.js') }}" defer></script>
    @if(request()->routeIs('listings.create','listings.edit'))<script src="{{ asset('js/add-property.js') }}" defer></script>@endif
    <script src="{{ asset('js/dashboard.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('css/brand.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body id="dashboard-top" class="reference-property-dashboard reference-dashboard layout-wrap {{ request()->routeIs('listings.create','listings.edit') ? 'reference-property-form' : '' }}">
<aside class="reference-sidebar" id="property-sidebar" aria-label="Dashboard navigation">
    <a href="{{ route('home') }}" class="reference-logo"><img src="{{ asset('branding/indoreestate-logo-light.svg') }}" alt="indoreestate — Your address in Indore" width="166" height="48"></a>
    <div class="reference-profile"><p>Profile</p><a href="{{ route('profile.edit') }}"><img src="{{ auth()->user()->avatar_path ? route('media.avatar',auth()->user()) : asset('branding/avatar-placeholder.svg') }}" alt=""><span><small>Account</small><strong>{{ auth()->user()->email }}</strong></span></a></div>
    <p class="reference-menu-label">Menu</p>
    <nav>
        <a href="{{ route('dashboard') }}" @class(['active'=>request()->routeIs('dashboard')]) @if(request()->routeIs('dashboard')) aria-current="page" @endif><x-reference-icon name="Dashboards"/>Dashboards</a>
        <a href="{{ route('profile.edit') }}" @class(['active'=>request()->routeIs('profile.*')])><x-reference-icon name="Profile"/>Profile</a>
        <a href="{{ route('reviews.index') }}" @class(['active'=>request()->routeIs('reviews.*')])><x-reference-icon name="Reviews"/>Reviews</a>
        @if(auth()->user()->canList())<a href="{{ route('listings.index') }}" @class(['active'=>request()->routeIs('listings.index','listings.preview')])><x-reference-icon name="My properties"/>{{ auth()->user()->isAdmin() ? 'All Properties' : 'My Properties' }}</a>@endif
        <a href="{{ route('favorites.index') }}" @class(['active'=>request()->routeIs('favorites.index')])><x-reference-icon name="My favorite"/>My Favorite</a>
        <a href="{{ route('conversations.index') }}" @class(['active'=>request()->routeIs('conversations.*')])><x-reference-icon name="Message"/>Message <span id="unread-count" data-url="{{ route('dashboard.unread') }}"></span></a>
        @if(auth()->user()->canList())<a href="{{ route('listings.create') }}" @class(['active'=>request()->routeIs('listings.create','listings.edit')]) @if(request()->routeIs('listings.create','listings.edit')) aria-current="page" @endif><x-reference-icon name="Add Property"/>Add Property</a>@endif
        @if(auth()->user()->isAdmin())<a href="{{ route('admin.users') }}" @class(['active'=>request()->routeIs('admin.users')])><x-reference-icon name="Profile"/>User access</a><a href="{{ route('admin.inbox') }}" @class(['active'=>request()->routeIs('admin.inbox')])><x-reference-icon name="Message"/>Contacts & subscribers</a>@endif
        <form method="post" action="{{ route('logout') }}">@csrf<button><x-reference-icon name="Logout"/>Logout</button></form>
    </nav>
</aside>
<header class="reference-header">
    <a href="{{ route('home') }}" class="reference-mobile-brand"><img src="{{ asset('branding/indoreestate-logo.svg') }}" alt="indoreestate" width="166"></a>
    <button class="reference-menu-toggle" type="button" aria-label="Toggle dashboard sidebar" aria-expanded="false" aria-controls="property-sidebar"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 6h18M3 12h12M3 18h7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button>
    <nav class="reference-top-nav" aria-label="Website navigation">
        <a href="{{ route('home') }}">Home</a><a href="{{ route('properties.index') }}">Properties</a><a href="{{ url('/about-us.html') }}">About Us</a><a href="{{ route('contact') }}">Contact</a><a class="current" href="{{ route('dashboard') }}">Dashboard</a>
    </nav>
    <div class="reference-header-account"><a href="{{ route('profile.edit') }}"><img src="{{ auth()->user()->avatar_path ? route('media.avatar',auth()->user()) : asset('branding/avatar-placeholder.svg') }}" alt=""><strong>{{ auth()->user()->name }}</strong><span>⌄</span></a>@if(auth()->user()->canList())<a class="button" href="{{ route('listings.create') }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8 4h9l4 4v12H8V4Zm9 0v5h4M4 8H3v13h12" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>Submit Property</a>@else<a class="button" href="{{ route('properties.index') }}">Browse properties</a>@endif</div>
</header>
<div class="reference-backdrop" hidden></div>
<div class="reference-main">
    @if(session('status'))<div class="notice success" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="notice error" role="alert"><strong>Please check the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul><p class="small">Your entered details are preserved. Please reselect any new files before trying again.</p></div>@endif
    <main id="dashboard-content" class="main-content-inner">@yield('content')</main>
    <footer class="reference-dashboard-footer">Copyright © {{ now()->year }} indoreestate<a href="#dashboard-top" aria-label="Back to top">↑</a></footer>
</div>
</body>
</html>
