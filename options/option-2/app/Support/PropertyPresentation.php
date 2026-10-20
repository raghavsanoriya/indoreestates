<?php

namespace App\Support;

use App\Http\Requests\SavePropertyRequest;
use App\Models\Property;
use Illuminate\Support\Str;

class PropertyPresentation
{
    public static function tourUrl(string $input): ?string
    {
        $input = trim($input);
        if (str_contains($input, '<')) {
            if (! preg_match('/^\s*<iframe\b[^>]*\bsrc=["\']([^"\']+)["\'][^>]*>\s*<\/iframe>\s*$/i', $input, $matches)) {
                return null;
            }
            $input = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
        }
        $parts = parse_url($input);
        $host = mb_strtolower($parts['host'] ?? '');
        if (($parts['scheme'] ?? '') !== 'https' || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            return null;
        }
        $hosts = ['my.matterport.com', 'kuula.co', 'www.kuula.co', 'tour.panoee.com', 'tour.giraffe360.com'];
        if (! in_array($host, $hosts, true) && ! ($host === 'www.google.com' && str_starts_with($parts['path'] ?? '', '/maps/embed'))) {
            return null;
        }

        return filter_var($input, FILTER_VALIDATE_URL) ? $input : null;
    }

    public static function videoUrl(string $input): ?string
    {
        $parts = parse_url($input);
        if (($parts['scheme'] ?? '') !== 'https' || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            return null;
        }
        $host = mb_strtolower($parts['host'] ?? '');
        if (in_array($host, ['youtube.com', 'www.youtube.com', 'youtu.be', 'www.youtube-nocookie.com'], true)) {
            parse_str($parts['query'] ?? '', $query);
            $id = $host === 'youtu.be' ? ltrim($parts['path'] ?? '', '/') : ($query['v'] ?? preg_replace('#^/(embed|shorts)/#', '', $parts['path'] ?? ''));

            return is_string($id) && preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? 'https://www.youtube-nocookie.com/embed/'.$id : null;
        }
        if (in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true) && preg_match('#^/(?:video/)?(\d+)$#', $parts['path'] ?? '', $matches)) {
            return 'https://player.vimeo.com/video/'.$matches[1];
        }

        return null;
    }

    /** @param array<int,string> $stored */
    public static function save(SavePropertyRequest $request, Property $property, array &$stored): void
    {
        if (! $request->has('presentation')) {
            return;
        }
        $details = $request->validated('presentation', []);
        $existing = $property->presentation ?? [];
        $details['tour_url'] = ! empty($details['tour_url']) ? self::tourUrl($details['tour_url']) : null;
        $details['video_url'] = ! empty($details['video_url']) ? self::videoUrl($details['video_url']) : null;
        $details['tour_image'] = $existing['tour_image'] ?? null;
        if ($request->hasFile('presentation_files.tour')) {
            $path = $request->file('presentation_files.tour')->store('property-presentation', 'local');
            if (! $path) {
                throw new \RuntimeException('Unable to store virtual tour image.');
            }
            $details['tour_image'] = $path;
            $stored[] = $path;
        }
        $oldFloors = collect($existing['floors'] ?? [])->keyBy('id');
        $floors = [];
        if (! empty($details['floors_enabled'])) {
            foreach ($details['floors'] ?? [] as $index => $floor) {
                $floor['id'] = $floor['id'] ?? (string) Str::uuid();
                $floor['image'] = data_get($oldFloors->get($floor['id']), 'image');
                if ($request->hasFile('presentation_files.floors.'.$index)) {
                    $path = $request->file('presentation_files.floors.'.$index)->store('property-presentation', 'local');
                    if (! $path) {
                        throw new \RuntimeException('Unable to store floor plan image.');
                    }
                    $floor['image'] = $path;
                    $stored[] = $path;
                }
                $floors[] = $floor;
            }
        }
        $details['floors'] = $floors;
        $property->presentation = $details;
    }
}
