<!doctype html>
<html lang="en"><head>@include('partials.head')</head>
<body class="@yield('body-class')"><div class="main-wrapper">
@include('partials.original-header')
@include('partials.notices')
<main id="main-content">@yield('content')</main>
@if(request()->routeIs('home'))
    @include('partials.home-two-footer')
@else
    @include('partials.original-footer')
@endif
</div>@include('partials.scripts')</body></html>
