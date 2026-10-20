@extends('layouts.dashboard')
@section('dashboard-content')
<div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">{{ auth()->user()->isAdmin()?'All Properties':'My Properties' }}</h5><a class="btn btn-primary" href="{{ route('listings.create') }}">Add Property</a></div><div class="card-body">@include('dashboard.partials.listing-filters')@include('dashboard.partials.listing-table')</div></div>
@endsection
