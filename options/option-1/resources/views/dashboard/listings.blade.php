@extends('layouts.dashboard')
@section('content')
<div class="widget-box-2 wd-listing">
    <div class="results-heading"><h5 class="title">{{ auth()->user()->isAdmin() ? 'All Properties' : 'My Properties' }}</h5><a class="button" href="{{ route('listings.create') }}">＋ Add property</a></div>
    <form class="wd-filter" method="get" action="{{ route('listings.index') }}">
        <div class="ip-group icon-left"><input type="search" name="keyword" value="{{ request('keyword') }}" placeholder="Search" aria-label="Search listings"><button class="search-submit" type="submit" aria-label="Search listings"><span class="icon icon-search"></span></button></div>
        <div class="ip-group"><input type="date" name="from" value="{{ request('from') }}" aria-label="Listings from date" max="{{ today()->toDateString() }}" data-dashboard-auto-submit></div>
        <div class="ip-group"><input type="date" name="to" value="{{ request('to') }}" aria-label="Listings to date" max="{{ today()->toDateString() }}" data-dashboard-auto-submit></div>
        <div class="ip-group"><select name="status" aria-label="Approval status" data-dashboard-auto-submit><option value="">All statuses</option>@foreach(['draft','pending','published','rejected'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
    </form>
    <div class="d-flex gap-4"><span class="text-primary fw-7">{{ number_format($properties->total()) }}</span><span class="fw-6">Results found</span></div>
    @include('dashboard.partials.listing-table', ['properties'=>$properties])
</div>
@endsection
