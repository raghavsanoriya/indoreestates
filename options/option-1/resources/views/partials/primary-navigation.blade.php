<li @class(['current' => request()->routeIs('home')])><a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a></li>
<li @class(['current' => request()->routeIs('properties.*')])><a href="{{ route('properties.index') }}" @if(request()->routeIs('properties.*')) aria-current="page" @endif>Properties</a></li>
<li @class(['current' => request()->is('about-us.html')])><a href="{{ url('/about-us.html') }}" @if(request()->is('about-us.html')) aria-current="page" @endif>About Us</a></li>
<li @class(['current' => request()->routeIs('contact')])><a href="{{ route('contact') }}" @if(request()->routeIs('contact')) aria-current="page" @endif>Contact</a></li>
@auth
<li @class(['current' => request()->is('dashboard', 'dashboard/*')])><a href="{{ route('dashboard') }}" @if(request()->is('dashboard', 'dashboard/*')) aria-current="page" @endif>Dashboard</a></li>
@endauth
