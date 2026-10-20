<div class="dropdown o3-account-dropdown" data-account-dropdown>
    <button class="o3-account-trigger" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" aria-controls="account-navigation" aria-label="Account menu" title="{{ auth()->user()->name }}">
        <img class="o3-account-avatar" src="{{ auth()->user()->avatar_path ? route('media.avatar',auth()->user()) : asset('branding/avatar-placeholder.svg') }}" width="48" height="48" alt="">
        <span class="o3-account-name">{{ auth()->user()->name }}</span>
    </button>
    <nav class="dropdown-menu dropdown-menu-end o3-account-panel" id="account-navigation" aria-label="Account navigation">
        @foreach(['dashboard'=>['Dashboard','explore'],'profile.edit'=>['My Profile','account_circle'],'reviews.index'=>['Reviews','rate_review'],'conversations.index'=>['Message','forum']] as $route=>[$label,$icon])
            <a href="{{ route($route) }}" @class(['dropdown-item o3-account-link','is-active'=>request()->routeIs($route) || ($route==='conversations.index' && request()->routeIs('conversations.*'))]) @if(request()->routeIs($route)) aria-current="page" @endif><span class="material-icons-outlined" aria-hidden="true">{{ $icon }}</span><span>{{ $label }}</span>@if($route==='conversations.index')<span id="unread-count" data-url="{{ route('dashboard.unread') }}"></span>@endif</a>
        @endforeach
        @if(auth()->user()->canList())
            <a href="{{ route('listings.index') }}" @class(['dropdown-item o3-account-link','is-active'=>request()->routeIs('listings.index','listings.edit','listings.preview')]) @if(request()->routeIs('listings.index')) aria-current="page" @endif><span class="material-icons-outlined" aria-hidden="true">home</span><span>{{ auth()->user()->isAdmin()?'All Properties':'My Properties' }}</span></a>
        @endif
        <a href="{{ route('favorites.index') }}" @class(['dropdown-item o3-account-link','is-active'=>request()->routeIs('favorites.*')]) @if(request()->routeIs('favorites.*')) aria-current="page" @endif><span class="material-icons-outlined" aria-hidden="true">favorite_border</span><span>My Favorite</span></a>
        @if(auth()->user()->canList())
            <a href="{{ route('listings.create') }}" @class(['dropdown-item o3-account-link','is-active'=>request()->routeIs('listings.create')]) @if(request()->routeIs('listings.create')) aria-current="page" @endif><span class="material-icons-outlined" aria-hidden="true">add_box</span><span>Submit Property</span></a>
        @endif
        <a class="dropdown-item o3-account-link" href="{{ route('profile.edit') }}#change-password"><span class="material-icons-outlined" aria-hidden="true">vpn_key</span><span>Change Password</span></a>
        @if(auth()->user()->isAdmin())
            <a class="dropdown-item o3-account-link" href="{{ route('admin.users') }}"><span class="material-icons-outlined" aria-hidden="true">group</span><span>User Access</span></a>
            <a class="dropdown-item o3-account-link" href="{{ route('admin.inbox') }}"><span class="material-icons-outlined" aria-hidden="true">inbox</span><span>Contacts & Subscribers</span></a>
        @endif
        <form method="post" action="{{ route('logout') }}">@csrf<button class="dropdown-item o3-account-link" type="submit"><span class="material-icons-outlined" aria-hidden="true">logout</span><span>Logout</span></button></form>
    </nav>
</div>
