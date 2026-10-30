<section class="mobile-detail-hero" aria-label="Property photos">
    <div class="mobile-detail-photos" tabindex="0" aria-label="Swipe to browse property photos">
        @forelse($property->photos as $photo)
            <button class="mobile-detail-photo" type="button" data-gallery-src="{{ $photo->url() }}" aria-label="Enlarge {{ $property->title }} image {{ $loop->iteration }}">
                <img src="{{ $photo->url() }}" alt="{{ $property->title }}" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif>
            </button>
        @empty
            <div class="mobile-detail-photo"><img src="{{ asset('branding/property-placeholder.svg') }}" alt="No photos yet"></div>
        @endforelse
    </div>
    <a href="{{ route('properties.index') }}" class="mobile-detail-back" aria-label="Back to properties"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14 5-7 7 7 7"/></svg></a>
    @if(!$preview)
        @auth
            <form class="mobile-detail-save" method="post" action="{{ $saved ? route('favorites.destroy', $property) : route('favorites.store', $property) }}">
                @csrf
                @if($saved) @method('DELETE') @endif
                <button type="submit" aria-label="{{ $saved ? 'Remove favourite' : 'Save favourite' }}" @class(['is-saved' => $saved])><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></svg></button>
            </form>
        @else
            <a class="mobile-detail-save" href="{{ route('login') }}" aria-label="Sign in to save property"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></svg></a>
        @endauth
    @endif
    <span class="mobile-detail-photo-count">{{ $property->photos->isEmpty() ? 'Photos coming soon' : $property->photos->count().' '.($property->photos->count() === 1 ? 'photo' : 'photos') }}</span>
</section>
