<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard.reviews', ['reviews' => Review::with(['author', 'property'])->when(! $request->user()->isAdmin(), fn ($q) => $q->where(fn ($q) => $q->where('user_id', $request->user()->id)->orWhereHas('property', fn ($q) => $q->where('user_id', $request->user()->id))))->latest()->paginate(15)]);
    }

    public function store(Request $request, Property $property): RedirectResponse
    {
        abort_unless($property->isPublic(), 404);
        abort_if($request->user()->id === $property->user_id, 403);
        $data = $request->validate(['rating' => ['required', 'integer', 'between:1,5'], 'body' => ['required', 'string', 'max:3000']]);
        $review = $property->reviews()->firstOrNew(['user_id' => $request->user()->id]);
        $review->user_id = $request->user()->id;
        $review->fill($data);
        $review->status = 'pending';
        $review->save();

        return back()->with('status', 'Review saved and awaiting administrator approval.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        Gate::authorize('delete', $review);
        $review->delete();

        return back()->with('status', 'Review removed.');
    }

    public function moderate(Request $request, Review $review): RedirectResponse
    {
        Gate::authorize('moderate', $review);
        $data = $request->validate(['status' => ['required', 'in:approved,rejected']]);
        $review->status = $data['status'];
        $review->save();

        return back()->with('status','Review moderation saved.');
    }
}
