<header id="header" class="main-header header-fixed fixed-header">
    <div class="header-lower"><div class="row"><div class="col-lg-12"><div class="inner-header">
        <div class="inner-header-left">
            <div class="logo-box flex"><div class="logo">
                <a href="{{ route('home') }}" aria-label="indoreestate home">
                    <img alt="indoreestate" loading="eager" width="200" height="44" class="logo-1" src="{{ asset('branding/indoreestate-logo.svg') }}">
                    <img alt="indoreestate" loading="eager" width="200" height="44" class="logo-2" src="{{ asset('branding/indoreestate-logo-light.svg') }}">
                </a>
            </div></div>
            <div class="nav-outer flex align-center">
                <nav class="main-menu show navbar-expand-md" aria-label="Website navigation">
                    <div class="navbar-collapse collapse clearfix">
                        <ul class="navigation clearfix">@include('partials.primary-navigation')</ul>
                    </div>
                </nav>
            </div>
        </div>
        <div class="inner-header-right header-account">
            @auth
                <a href="{{ route('profile.edit') }}" class="tf-btn btn-line btn-login">{{ auth()->user()->name }}</a>
                <form method="post" action="{{ route('logout') }}">@csrf<button class="tf-btn primary">Sign out</button></form>
            @else
                <a href="{{ route('login') }}" class="tf-btn btn-line btn-login"><i class="icon icon-user"></i> Sign in</a>
                <div class="flat-bt-top"><a class="tf-btn primary" href="{{ route('listings.create') }}"><i class="icon icon-file"></i> Submit Property</a></div>
            @endauth
        </div>
        <button type="button" class="mobile-nav-toggler mobile-button" aria-label="Open navigation" aria-controls="mobile-site-navigation" aria-expanded="false"><span></span></button>
    </div></div></div></div>
    <button type="button" class="close-btn" aria-label="Close navigation"><span class="icon flaticon-cancel-1"></span></button>
    <div class="mobile-menu" id="mobile-site-navigation">
        <div class="menu-backdrop"></div>
        <nav class="menu-box" aria-label="Mobile navigation">
            <div class="nav-logo"><a href="{{ route('home') }}" aria-label="indoreestate home"><img alt="indoreestate" loading="eager" width="200" height="44" src="{{ asset('branding/indoreestate-logo.svg') }}"></a></div>
            <div class="bottom-canvas">
                <div class="login-box flex align-center">
                    @auth
                        <a href="{{ route('profile.edit') }}">{{ auth()->user()->name }}</a>
                        <form method="post" action="{{ route('logout') }}">@csrf<button type="submit" class="mobile-signout">Sign out</button></form>
                    @else
                        <a href="{{ route('login') }}">Sign in</a><span>/</span><a href="{{ route('register') }}">Register</a>
                    @endauth
                </div>
                <div class="menu-outer"><div class="navbar-collapse clearfix"><ul class="navigation clearfix mobile-nav">@include('partials.primary-navigation')</ul></div></div>
                <div class="button-mobi-sell">
                    @if(!auth()->check() || auth()->user()->canList())
                        <a class="tf-btn primary" href="{{ route('listings.create') }}">Submit Property</a>
                    @else
                        <a class="tf-btn primary" href="{{ route('properties.index') }}">Browse Properties</a>
                    @endif
                </div>
                <div class="mobi-icon-box">
                    <div class="box d-flex align-items-center"><span class="icon icon-phone2"></span><a href="{{ route('contact') }}">Contact our team</a></div>
                    <div class="box d-flex align-items-center"><span class="icon icon-mail"></span><span>indoreestate</span></div>
                </div>
            </div>
        </nav>
    </div>
</header>
