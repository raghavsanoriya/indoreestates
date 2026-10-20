<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name').' · Find your dream home' }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('branding/favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('template/_next/static/css/2ac4ac2d5afb7e7c.css') }}">
    <link rel="stylesheet" href="{{ asset('template/_next/static/css/98298e6c021b1ea1.css') }}">
    <link rel="stylesheet" href="{{ asset('css/estate.css') }}">
    <script src="{{ asset('js/estate.js') }}" defer></script>
    <meta name="description" content="Find your next home with indoreestate. Browse properties and connect with property agents.">
    <meta name="theme-color" content="#1563df">
    <link rel="stylesheet" href="{{ asset('css/brand.css') }}">
</head>
<body class="{{ request()->routeIs('home') ? 'original-home-page' : '' }}">
@include('partials.original-header')
@if(session('status'))<div class="notice success" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="notice error" role="alert"><strong>Please check the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if(request()->routeIs('dashboard','listings.*','favorites.index','conversations.*','reviews.index','profile.*','admin.*'))
<div class="dashboard-shell">
    <aside class="dashboard-nav">
        <div class="account-card">
            <img class="avatar" src="{{ auth()->user()->avatar_path ? route('media.avatar', auth()->user()) : asset('branding/avatar-placeholder.svg') }}" alt="">
            <strong>{{ auth()->user()->name }}</strong><span>{{ ucfirst(auth()->user()->role) }} account</span>
        </div>
        <a href="{{ route('dashboard') }}" @class(['active'=>request()->routeIs('dashboard')])>◫ Overview</a>
        @if(auth()->user()->canList())
        <a href="{{ route('listings.index') }}" @class(['active'=>request()->routeIs('listings.*')])>⌂ {{ auth()->user()->isAdmin() ? 'All properties' : 'My properties' }}</a>
        <a href="{{ route('listings.create') }}">＋ Add property</a>
        @endif
        <a href="{{ route('conversations.index') }}" @class(['active'=>request()->routeIs('conversations.*')])>✉ Messages <span id="unread-count" data-url="{{ route('dashboard.unread') }}"></span></a>
        <a href="{{ route('favorites.index') }}" @class(['active'=>request()->routeIs('favorites.index')])>♡ My favorites</a>
        <a href="{{ route('reviews.index') }}" @class(['active'=>request()->routeIs('reviews.*')])>☆ Reviews</a>
        <a href="{{ route('profile.edit') }}" @class(['active'=>request()->routeIs('profile.*')])>◉ My profile</a>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.users') }}">♙ User access</a><a href="{{ route('admin.inbox') }}">✉ Contacts & subscribers</a>
        @endif
        <div class="sidebar-help"><strong>Find your next chapter.</strong><p>Manage your real estate activity in one place.</p><a href="{{ route('properties.index') }}">Explore properties →</a></div>
    </aside>
    <main class="dashboard-main">@yield('content')</main>
</div>
@else
<main>@yield('content')</main>
@endif
@include('partials.original-footer')
</body></html>
