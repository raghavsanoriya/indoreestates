<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\Property;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PropertyController extends Controller
{
    public function home(Request $request): View
    {
        $filters = $request->validate(['type' => ['nullable', 'in:Apartment,Villa,House,Studio,Office,Land']]);
        $listings = Property::visible()->withPublicRatings()->with(['photos', 'owner'])
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type));

        return view('properties.home', [
            'properties' => (clone $listings)->latest()->orderByDesc('id')->limit(8)->get(),
            'saleProperties' => (clone $listings)->where('purpose', 'sale')->latest()->orderByDesc('id')->limit(8)->get(),
            'rentProperties' => (clone $listings)->where('purpose', 'rent')->latest()->orderByDesc('id')->limit(8)->get(),
            'locationProjects' => Property::visible()->with(['photos', 'owner'])->latest()->orderByDesc('id')->limit(4)->get(),
            'reviews' => Review::where('status', 'approved')->whereHas('property', fn ($query) => $query->visible())->with('author')->latest()->limit(3)->get(),
            'typeCounts' => Property::visible()->select('type')->selectRaw('count(*) as total')->groupBy('type')->pluck('total', 'type'),
            'total' => Property::visible()->count(),
            'saleCount' => Property::visible()->where('purpose', 'sale')->count(),
            'rentCount' => Property::visible()->where('purpose', 'rent')->count(),
        ]);
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'keyword' => ['nullable', 'string', 'max:160'], 'location' => ['nullable', 'string', 'max:100'],
            'purpose' => ['nullable', 'in:sale,rent'], 'type' => ['nullable', 'in:Apartment,Villa,House,Studio,Office,Land'],
            'min_price' => ['nullable', 'numeric', 'min:0'], 'max_price' => ['nullable', 'numeric', 'min:0'],
            'bedrooms' => ['nullable', 'integer', 'between:0,100'], 'bathrooms' => ['nullable', 'integer', 'between:0,100'],
            'min_area' => ['nullable', 'numeric', 'min:0'], 'max_area' => ['nullable', 'numeric', 'min:0'],
            'amenities' => ['nullable', 'array', 'max:30'], 'amenities.*' => ['integer', 'exists:amenities,id'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc'], 'view' => ['nullable', 'in:grid,list,map'],
        ]);
        $query = Property::visible()->withPublicRatings()->with(['photos', 'owner']);
        foreach (['keyword' => ['title', 'description', 'address'], 'location' => ['location', 'neighborhood', 'state', 'address']] as $key => $columns) {
            if (! empty($filters[$key])) {
                $query->where(function (Builder $q) use ($filters, $key, $columns): void {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'like', '%'.$filters[$key].'%');
                    }
                });
            }
        }
        foreach (['purpose', 'type'] as $key) {
            if (! empty($filters[$key])) {
                $query->where($key, $filters[$key]);
            }
        }
        foreach (['min_price' => ['price', '>='], 'max_price' => ['price', '<='], 'min_area' => ['area', '>='], 'max_area' => ['area', '<='], 'bedrooms' => ['bedrooms', '>='], 'bathrooms' => ['bathrooms', '>=']] as $key => [$column,$operator]) {
            if (isset($filters[$key])) {
                $query->where($column, $operator, $filters[$key]);
            }
        }
        foreach ($filters['amenities'] ?? [] as $amenity) {
            $query->whereHas('amenities', fn ($q) => $q->where('amenities.id', $amenity));
        }
        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderByRaw('price IS NULL')->orderBy('price'),
            'price_desc' => $query->orderByRaw('price IS NULL')->orderByDesc('price'),
            default => $query->orderByDesc('created_at'),
        };

        return view('properties.index', ['properties' => $query->orderByDesc('id')->paginate(12)->withQueryString(), 'amenities' => Amenity::orderBy('name')->get(), 'filters' => $filters]);
    }

    public function show(Request $request, Property $property): View
    {
        abort_unless($property->isPublic(), 404);
        if (! $request->session()->has('viewed.'.$property->id.'.'.today()->toDateString())) {
            DB::table('property_views')->insertOrIgnore(['property_id' => $property->id, 'day' => today()->toDateString(), 'views' => 0, 'created_at' => now(), 'updated_at' => now()]);
            $property->views()->where('day', today()->toDateString())->increment('views');
            $request->session()->put('viewed.'.$property->id.'.'.today()->toDateString(), true);
        }

        return $this->detail($request, $property, false);
    }

    public function preview(Request $request, Property $property): View
    {
        Gate::authorize('view', $property);

        return $this->detail($request, $property, true);
    }

    private function detail(Request $request, Property $property, bool $preview): View
    {
        $property->load(['photos', 'owner', 'amenities', 'reviews' => fn ($q) => $q->where('status', 'approved')->with('author')->latest()]);

        return view('properties.show', [
            'property' => $property, 'preview' => $preview,
            'related' => Property::visible()->withPublicRatings()->with(['photos', 'owner'])->where('id', '!=', $property->id)->where('type', $property->type)->limit(3)->get(),
            'saved' => $request->user()?->favorites()->where('properties.id', $property->id)->exists() ?? false,
            'ownReview' => $request->user() ? $property->reviews()->where('user_id', $request->user()->id)->first() : null,
        ]);
    }

    public function legacy(string $id): RedirectResponse
    {
        $property = Property::visible()->where('legacy_id', $id)->firstOrFail();

        return redirect()->route('properties.show', $property);
    }
}
