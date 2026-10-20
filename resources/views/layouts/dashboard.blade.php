@extends('layouts.app')
@section('body-class','dashboard-page')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush
@push('scripts')
<script src="{{ asset('js/dashboard.js') }}" defer></script>
@endpush
@section('content')
@php
    $workspaceTitle=$dashboardTitle ?? match(true) {
        request()->routeIs('listings.*') => auth()->user()->isAdmin()?'All Properties':'My Properties',
        request()->routeIs('favorites.*') => 'My Favourites',
        request()->routeIs('conversations.*') => 'Messages',
        request()->routeIs('reviews.*') => 'Reviews',
        request()->routeIs('profile.*') => 'My Profile',
        request()->routeIs('admin.users') => 'User Access',
        request()->routeIs('admin.inbox') => 'Contacts & Subscribers',
        default => 'Dashboard',
    };
@endphp
<div class="content dashboard-workspace"><div class="workspace-layout">
<aside class="workspace-sidebar-column" id="workspace-sidebar" aria-label="Property workspace"><div class="workspace-sidebar">
<div class="workspace-account"><img class="avatar rounded-circle" src="{{ auth()->user()->avatar_path ? route('media.avatar',auth()->user()) : asset('branding/avatar-placeholder.svg') }}" alt=""><div><h6>{{ auth()->user()->name }}</h6><span class="workspace-role">{{ ucfirst(auth()->user()->role) }} workspace</span></div><button class="workspace-menu-toggle" type="button" data-workspace-toggle aria-expanded="false" aria-controls="workspace-sidebar" aria-label="Toggle dashboard menu"><span class="material-icons-outlined" aria-hidden="true">close</span></button></div>
<div id="workspace-navigation"><p class="workspace-nav-label">YOUR WORKSPACE</p><nav aria-label="Dashboard navigation" class="workspace-nav">
@foreach(['dashboard'=>['Overview','space_dashboard'],'favorites.index'=>['My Favourites','favorite_border'],'conversations.index'=>['Messages','chat_bubble_outline'],'reviews.index'=>['Reviews','star_outline'],'profile.edit'=>['My Profile','person_outline']] as $route=>[$label,$icon])
<a href="{{ route($route) }}" @class(['workspace-nav-link','is-active'=>request()->routeIs($route) || ($route==='conversations.index' && request()->routeIs('conversations.*'))]) @if(request()->routeIs($route)) aria-current="page" @endif><span class="material-icons-outlined" aria-hidden="true">{{ $icon }}</span>{{ $label }}</a>
@endforeach
@if(auth()->user()->canList())
<p class="workspace-nav-label">PROPERTY MANAGEMENT</p><a href="{{ route('listings.index') }}" @class(['workspace-nav-link','is-active'=>request()->routeIs('listings.index','listings.edit','listings.preview')]) @if(request()->routeIs('listings.index')) aria-current="page" @endif><span class="material-icons-outlined" aria-hidden="true">apartment</span>{{ auth()->user()->isAdmin()?'All Properties':'My Properties' }}</a><a href="{{ route('listings.create') }}" @class(['workspace-nav-link','is-active'=>request()->routeIs('listings.create')]) @if(request()->routeIs('listings.create')) aria-current="page" @endif><span class="material-icons-outlined" aria-hidden="true">add_circle_outline</span>Add Property</a>
@endif
@if(auth()->user()->isAdmin())
<p class="workspace-nav-label">ADMINISTRATION</p><a href="{{ route('admin.users') }}" @class(['workspace-nav-link','is-active'=>request()->routeIs('admin.users')]) @if(request()->routeIs('admin.users')) aria-current="page" @endif><span class="material-icons-outlined" aria-hidden="true">group</span>User Access</a><a href="{{ route('admin.inbox') }}" @class(['workspace-nav-link','is-active'=>request()->routeIs('admin.inbox')]) @if(request()->routeIs('admin.inbox')) aria-current="page" @endif><span class="material-icons-outlined" aria-hidden="true">inbox</span>Contacts & Subscribers</a>
@endif
<form action="{{ route('logout') }}" method="post">@csrf<button class="workspace-nav-link workspace-signout"><span class="material-icons-outlined" aria-hidden="true">logout</span>Sign Out</button></form>
</nav><a class="workspace-site-link" href="{{ route('home') }}"><span class="material-icons-outlined" aria-hidden="true">open_in_new</span>View your website<span aria-hidden="true">↗</span></a></div>
</div></aside><div class="workspace-main">@include('partials.notices')<x-breadcrumb :title="$workspaceTitle"/>@yield('dashboard-content')</div>
</div></div><button class="workspace-sidebar-backdrop" type="button" data-workspace-close aria-label="Close dashboard sidebar" tabindex="-1"></button>
<dialog id="workspace-confirm" class="workspace-confirm"><form method="dialog"><span class="workspace-confirm-icon material-icons-outlined" aria-hidden="true">delete_outline</span><h3>Move listing to trash?</h3><p data-confirm-copy></p><div class="d-flex gap-2 justify-content-end"><button class="btn btn-light" value="cancel">Keep listing</button><button class="btn btn-danger" type="button" data-confirm-submit>Move to trash</button></div></form></dialog>
@endsection
