<nav class="mobile-dock" aria-label="Mobile navigation">
    <a href="{{ route('home') }}" class="mobile-dock-link" @if(request()->routeIs('home')) aria-current="page" @endif>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1Z"/></svg>
        <span>Home</span>
    </a>
    <a href="{{ route('properties.index') }}" class="mobile-dock-link" @if(request()->routeIs('properties.*')) aria-current="page" @endif>
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg>
        <span>Explore</span>
    </a>
    <a href="{{ auth()->check() && ! auth()->user()->canList() ? route('contact', ['subject' => 'Property listing access']) : route('listings.create') }}" class="mobile-dock-link mobile-dock-add" aria-label="Add property{{ auth()->check() && ! auth()->user()->canList() ? ' — contact us for agent access' : '' }}" @if(request()->routeIs('listings.create')) aria-current="page" @endif>
        <span class="mobile-dock-plus"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span>
        <span>Add property</span>
    </a>
    <a href="{{ route('favorites.index') }}" class="mobile-dock-link" @if(request()->routeIs('favorites.*')) aria-current="page" @endif>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></svg>
        <span>Saved</span>
    </a>
    <a href="{{ route('conversations.index') }}" class="mobile-dock-link" @if(request()->routeIs('conversations.*')) aria-current="page" @endif>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5H4l-3 2V11.5A8.5 8.5 0 0 1 9.5 3h3a8.5 8.5 0 0 1 8.5 8.5Z"/><path d="M6 10h10M6 14h6"/></svg>
        <span>Messages</span>
    </a>
</nav>
