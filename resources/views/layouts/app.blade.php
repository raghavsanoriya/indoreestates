<!doctype html>
<html lang="en"><head>@include('partials.head')
<link rel="stylesheet" href="{{ asset('css/mobile-app.css') }}?v=20261030-4">
</head>
<body class="elementor-default elementor-kit-6 @yield('body-class')"><div class="main-wrapper">
@include('partials.original-header')
@hasSection('dashboard-content')@else @include('partials.notices')@endif
<main id="main-content">@yield('content')</main>
@hasSection('dashboard-content')
<footer class="workspace-footer"><span>© {{ now()->year }} indoreestate. All rights reserved.</span><a href="{{ route('captured-page.show','privacy-policy.html') }}">Privacy</a></footer>
@else
@include('partials.original-footer')
@endif
</div>@include('partials.mobile-navigation')
@include('partials.scripts')
<script src="{{ asset('js/mobile-app.js') }}?v=20261030" defer></script>
</body></html>
