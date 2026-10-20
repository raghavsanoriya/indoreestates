@extends('layouts.dashboard')
@section('content')
<button type="button" class="show-dashboard-button" data-show-dashboard>Show Dashboard</button>
<div class="flat-counter-v2 tf-counter">
    @foreach([['key'=>'Listings','label'=>'Your listing','icon'=>'listing'],['key'=>'Pending approval','label'=>'Pending','icon'=>'pending'],['key'=>'Favorites','label'=>'Favorites','icon'=>'favorite'],['key'=>'Reviews','label'=>'Reviews','icon'=>'review']] as $card)
    <div class="counter-box"><div class="box-icon"><span class="icon icon-{{ $card['icon'] }}" aria-hidden="true"></span></div><div class="content-box"><div class="title-count text-variant-1">{{ $card['label'] }}</div><div class="box-count d-flex align-items-end"><h3 class="fw-8">{{ number_format($counts[$card['key']]) }}</h3></div></div></div>
    @endforeach
</div>
@if(auth()->user()->role==='customer')<div class="widget-box-2 mb-20"><h5 class="title">Your next home is waiting</h5><p>Browse homes, save your favorites, and start a private conversation with an agent. To list properties, contact the administrator for agent access.</p><a class="button" href="{{ route('properties.index') }}">Explore properties ↗</a></div>@endif
<div class="wrapper-content row">
    <div class="col-xl-9">
        @if(auth()->user()->canList())
        <div class="widget-box-2 wd-listing">
            <h5 class="title">New Listing</h5>
            <form class="wd-filter" method="get" action="{{ route('dashboard') }}">
                @foreach(request()->only(['period','chart_from','chart_to']) as $key=>$value)@if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif @endforeach
                <div class="ip-group icon-left"><input type="search" name="keyword" value="{{ request('keyword') }}" placeholder="Search" aria-label="Search listings"><button type="submit" class="search-submit" aria-label="Search listings"><span class="icon icon-search"></span></button></div>
                <div class="ip-group"><input type="date" name="from" value="{{ request('from') }}" aria-label="Listings from date" max="{{ today()->toDateString() }}" data-dashboard-auto-submit></div>
                <div class="ip-group"><input type="date" name="to" value="{{ request('to') }}" aria-label="Listings to date" max="{{ today()->toDateString() }}" data-dashboard-auto-submit></div>
                <div class="ip-group"><select name="status" aria-label="Listing status" data-dashboard-auto-submit><option value="">All statuses</option>@foreach(['draft','pending','published','rejected'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            </form>
            <div class="d-flex gap-4"><span class="text-primary fw-7">{{ number_format($properties->total()) }}</span><span class="fw-6">Results found</span></div>
            @include('dashboard.partials.listing-table', ['properties'=>$properties])
        </div>
        @endif
        @include('dashboard.partials.view-chart')
    </div>
    <div class="col-xl-3">
        <div class="widget-box-2 mess-box mb-20">
            <div class="d-flex align-items-center mb-20"><h5 class="title mb-0">Messages</h5><a class="title-link" href="{{ route('conversations.index') }}">View all →</a></div>
            <ul class="list-mess">
            @forelse($conversations as $conversation)
                @php($peer=auth()->id()===$conversation->customer_id ? $conversation->agent : $conversation->customer)
                <li class="mess-item">
                    <a href="{{ route('conversations.show',$conversation) }}" class="user-box"><div class="avatar"><img src="{{ $peer->avatar_path ? route('media.avatar',$peer) : asset('branding/avatar-placeholder.svg') }}" alt="" width="34" height="34"></div><div class="content"><div class="name fw-6">{{ $peer->name }}</div><span class="caption-2 text-variant-3">{{ $conversation->updated_at->diffForHumans() }}</span></div></a>
                    <p>{{ Str::limit($conversation->messages->first()?->body ?? $conversation->property->title, 150) }}</p>
                </li>
            @empty<li class="dashboard-empty">No inquiries yet.</li>@endforelse
            </ul>
        </div>
        <div class="widget-box-2 mess-box">
            <div class="d-flex align-items-center mb-20"><h5 class="title mb-0">Recent Reviews</h5><a class="title-link" href="{{ route('reviews.index') }}">View all →</a></div>
            <ul class="list-mess">
            @forelse($recentReviews as $review)
                <li class="mess-item">
                    <div class="user-box"><div class="avatar"><img src="{{ $review->author->avatar_path ? route('media.avatar',$review->author) : asset('branding/avatar-placeholder.svg') }}" alt="" width="34" height="34"></div><div class="content"><div class="name fw-6">{{ $review->author->name }}</div><span class="caption-2 text-variant-3">{{ $review->created_at->diffForHumans() }}</span></div></div>
                    <p>{{ Str::limit($review->body,150) }}</p><ul class="list-star" aria-label="{{ $review->rating }} out of 5 stars">@for($star=1;$star<=5;$star++)<li @class(['icon','icon-star','inactive'=>$star>$review->rating]) aria-hidden="true"></li>@endfor</ul>
                    <span class="status {{ $review->status }}">{{ ucfirst($review->status) }}</span>
                </li>
            @empty<li class="dashboard-empty">No reviews yet.</li>@endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
