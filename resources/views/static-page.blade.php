@extends('layouts.app')
@section('title', $cms->pageTitle($slug))
@section('meta_description', $cms->pageDescription($slug))
@section('content')
<div class="page-wrapper"><x-breadcrumb :title="$title"/>
@if($slug==='faq')@include('partials.homez-faq')@else
<div class="content"><div class="container"><div class="row align-items-center">@if($slug==='about')<div class="col-lg-6 mb-4"><img class="img-fluid rounded" src="{{ $cms->image('pages.about.image') }}" alt=""></div>@endif<div class="{{ $slug==='about'?'col-lg-6':'col-lg-10 mx-auto' }}"><div class="card"><div class="card-body">
<div class="cms-content">{{ $cms->html("pages.$slug.body") }}</div>
<div class="d-flex gap-2 flex-wrap mt-4"><a class="btn btn-primary" href="{{ route('properties.index') }}">Explore Properties</a><a class="btn btn-outline-primary" href="{{ route('contact') }}">Contact Us</a></div></div></div></div></div></div></div>@endif</div>
@endsection
