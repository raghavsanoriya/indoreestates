<li @class(['active'=>request()->routeIs('home')])><a href="{{ route('home') }}">Home</a></li>
<li class="has-submenu"><a href="{{ route('properties.index') }}">Properties <i class="material-icons-outlined">expand_more</i></a><ul class="submenu"><li><a href="{{ route('properties.index') }}">All Properties</a></li><li><a href="{{ route('properties.index',['purpose'=>'sale']) }}">For Sale</a></li><li><a href="{{ route('properties.index',['purpose'=>'rent']) }}">For Rent</a></li></ul></li>
<li><a href="{{ url('/about-us.html') }}">About Us</a></li><li><a href="{{ route('contact') }}">Contact Us</a></li>
@auth<li><a href="{{ route('dashboard') }}">Dashboard</a></li>@endauth
