<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePropertyRequest;
use App\Models\Amenity;
use App\Models\Property;
use App\Support\PropertyPresentation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ListingController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('create', Property::class);
        $filters = $request->validate(['keyword' => ['nullable', 'string', 'max:160'], 'status' => ['nullable', 'in:draft,pending,published,rejected'], 'from' => ['nullable', 'date', 'before_or_equal:today'], 'to' => ['nullable', 'date', 'before_or_equal:today']]);
        $query = Property::withTrashed()->with(['photos', 'owner'])->when(! $request->user()->isAdmin(), fn ($q) => $q->where('user_id', $request->user()->id));
        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%'.$request->string('keyword')->limit(160).'%');
        }
        if (in_array($request->query('status'), ['draft', 'pending', 'published', 'rejected'], true)) {
            $query->where('status', $request->query('status'));
        }
        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        return view('dashboard.listings', ['properties' => $query->latest()->orderByDesc('id')->paginate(10)->withQueryString()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Property::class);

        return view('dashboard.property-form', ['property' => new Property, 'amenities' => Amenity::orderBy('name')->get()]);
    }

    public function edit(Property $property): View
    {
        Gate::authorize('update', $property);

        return view('dashboard.property-form', ['property' => $property->load(['photos', 'amenities']), 'amenities' => Amenity::orderBy('name')->get()]);
    }

    public function store(SavePropertyRequest $request): RedirectResponse
    {
        $property = new Property;
        $property->user_id = $request->user()->id;
        $this->save($request, $property);

        if ($request->input('intent') === 'preview') {
            return redirect()->route('listings.preview', $property)->with('status', 'Draft saved. This preview is private.');
        }

        return redirect()->route('listings.index')->with('status', 'Property saved. '.($property->status === 'pending' ? 'It is awaiting administrator approval.' : 'It is a private draft.'));
    }

    public function update(SavePropertyRequest $request, Property $property): RedirectResponse
    {
        $this->save($request, $property);

        if ($request->input('intent') === 'preview') {
            return redirect()->route('listings.preview', $property)->with('status', 'Draft saved. This preview is private.');
        }

        return redirect()->route('listings.index')->with('status', 'Property updated. Submitted changes require administrator approval.');
    }

    private function save(SavePropertyRequest $request, Property $property): void
    {
        $stored = [];
        try {
            DB::transaction(function () use ($request, $property, &$stored): void {
                $property->fill($request->safe()->except(['intent', 'amenities', 'photos', 'remove_photos', 'photo_order', 'cover_photo', 'presentation', 'presentation_files', 'g-recaptcha-response']));
                PropertyPresentation::save($request, $property, $stored);
                $property->status = $request->input('intent') === 'submit' ? 'pending' : 'draft';
                $property->rejection_reason = null;
                $property->save();
                $property->amenities()->sync($request->input('amenities', []));
                $property->photos()->whereIn('id', $request->input('remove_photos', []))->delete();
                $order = $request->input('photo_order', []);
                if ($request->filled('cover_photo')) {
                    $order = array_merge([$request->integer('cover_photo')], array_diff($order, [$request->integer('cover_photo')]));
                }
                $remaining = $property->photos()->pluck('id')->all();
                $order = array_values(array_unique(array_merge($order, $remaining)));
                foreach ($order as $position => $id) {
                    $property->photos()->where('id', $id)->update(['position' => $position]);
                }
                $position = count($order);
                foreach ($request->file('photos', []) as $upload) {
                    $path = $upload->store('property-photos', 'local');
                    if (! $path) {
                        throw new \RuntimeException('Unable to store image.');
                    }
                    $stored[] = $path;
                    $property->photos()->create(['path' => $path, 'position' => $position++]);
                }
            });
        } catch (\Throwable $exception) {
            foreach ($stored as $path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }
    }

    public function destroy(Property $property): RedirectResponse
    {
        Gate::authorize('delete', $property);
        $property->delete();

        return back()->with('status', 'Listing moved to trash. You can restore it.');
    }

    public function restore(int $id): RedirectResponse
    {
        $property = Property::onlyTrashed()->findOrFail($id);
        Gate::authorize('restore', $property);
        $property->status = 'draft';
        $property->restore();

        return back()->with('status', 'Listing restored as a draft.');
    }

    public function moderate(Request $request, Property $property): RedirectResponse
    {
        Gate::authorize('moderate', $property);
        $data = $request->validate(['decision' => ['required', 'in:published,rejected'], 'rejection_reason' => ['nullable', 'required_if:decision,rejected', 'string', 'max:2000']]);
        if ($data['decision'] === 'published' && ! $property->isInIndore()) {
            return back()->withErrors(['location' => 'Only properties in Indore can be published.']);
        }
        if ($data['decision'] === 'published' && ! $property->photos()->exists()) {
            return back()->withErrors(['photos' => 'A listing needs at least one photo before publishing.']);
        }
        $property->status = $data['decision'];
        $property->rejection_reason = $data['decision'] === 'rejected' ? $data['rejection_reason'] : null;
        $property->save();

        return back()->with('status', 'Listing '.($property->status === 'published' ? 'approved and published.' : 'rejected with feedback.'));
    }
}
