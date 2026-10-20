<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    public function presentation(Request $request, Property $property, string $kind, string $index = '0'): StreamedResponse
    {
        abort_unless($property->isPublic() || ($request->user() && Gate::allows('view', $property)), 404);
        $path = $kind === 'tour' ? data_get($property->presentation, 'tour_image') : data_get($property->presentation, 'floors.'.(int) $index.'.image');
        abort_unless(is_string($path) && preg_match('#^property-presentation/[a-zA-Z0-9]+\.(jpg|jpeg|png|webp)$#', $path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }

    public function photo(Request $request, PropertyPhoto $photo): BinaryFileResponse|StreamedResponse
    {
        $property = $photo->property;
        abort_unless($property && ($property->isPublic() || ($request->user() && Gate::allows('view', $property))), 404);
        if ($photo->is_demo) {
            abort_unless(preg_match('#^images/home/house-\d+\.jpg$#', $photo->path), 404);

            return response()->file(public_path('template/'.$photo->path), ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
        }
        abort_unless(str_starts_with($photo->path, 'property-photos/') && Storage::disk('local')->exists($photo->path), 404);

        return Storage::disk('local')->response($photo->path, null, ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }

    public function avatar(User $user): StreamedResponse
    {
        abort_unless($user->avatar_path && str_starts_with($user->avatar_path, 'avatars/') && Storage::disk('local')->exists($user->avatar_path), 404);

        return Storage::disk('local')->response($user->avatar_path, null, ['X-Content-Type-Options' => 'nosniff']);
    }
}
