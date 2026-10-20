<!doctype html>
<html lang="en"><head>@include('partials.head')</head>
<body><div class="main-wrapper auth-cover"><div class="container-fluid position-relative z-1"><div class="w-100 overflow-hidden position-relative flex-wrap d-block min-vh-100"><div class="row justify-content-center align-items-center min-vh-100 overflow-auto flex-wrap py-3"><div class="col-md-8 col-lg-6 col-xl-4 mx-auto"><div class="d-flex flex-column p-4 p-lg-0 flex-fill">
<a href="{{ route('home') }}" class="mx-auto mb-4 text-center"><img src="{{ asset('branding/indoreestate-logo.svg') }}" alt="indoreestate" width="210" height="30"></a>
@include('partials.notices')
@yield('content')
</div></div></div></div></div></div>@include('partials.scripts')</body></html>
