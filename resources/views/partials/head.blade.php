<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="@yield('meta_description', $cms->text('seo.default.description'))">
<title>@yield('title', $cms->text('seo.default.title'))</title>
<link rel="icon" href="{{ asset('branding/favicon.svg') }}">
<link rel="preload" href="{{ asset('icons/material-icon-outlined.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="{{ asset('source/fonts.googleapis.com/css793e.css') }}">
<link rel="stylesheet" href="{{ asset('source/demoapus1.com/homez/wp-content/themes/homez/css/bootstrap55a0.css') }}">
<link rel="stylesheet" href="{{ asset('source/demoapus1.com/homez/wp-content/plugins/elementor/assets/css/frontend-lite.mina5bd.css') }}">
<link rel="stylesheet" href="{{ asset('source/demoapus1.com/homez/wp-content/uploads/elementor/css/post-60fe0.css') }}">
<link rel="stylesheet" href="{{ asset('source/demoapus1.com/homez/wp-content/themes/homez/css/template5152.css') }}">
<link rel="stylesheet" href="{{ asset('source/demoapus1.com/homez/wp-content/themes/homez/style5152.css') }}">
<link rel="stylesheet" href="{{ asset('source/demoapus1.com/homez/wp-content/uploads/elementor/css/post-19c8bb.css') }}">
<link rel="stylesheet" href="{{ asset('css/homez-original.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v=20261031">
<link rel="stylesheet" href="{{ asset('css/homez.css') }}?v=20261030-contact">
@stack('styles')
