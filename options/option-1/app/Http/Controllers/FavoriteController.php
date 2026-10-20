<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard.favorites', ['properties' => $request->user()->favorites()->visible()->with('photos')->orderByDesc('favorites.created_at')->paginate(12)]);
    }

    public function store(Request $request, Property $property): RedirectResponse
    {
        abort_unless($property->isPublic(), 404);
        $request->user()->favorites()->syncWithoutDetaching([$property->id]);

        return back()->with('status', 'Property saved to favorites.');
    }

    public function destroy(Request $request, Property $property): RedirectResponse
    {
        $request->user()->favorites()->detach($property->id);

        return back()->with('status', 'Property removed from favorites.');
    }
}
