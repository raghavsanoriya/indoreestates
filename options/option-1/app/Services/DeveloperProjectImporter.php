<?php

namespace App\Services;

use App\Models\Property;
use App\Models\User;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DeveloperProjectImporter
{
    public const SOURCES = [
        'ruchi' => ['name' => 'Ruchi Realty', 'url' => 'https://ruchirealty.com/'],
        'emerald' => ['name' => 'Emerald Developers', 'url' => 'https://emeralddevelopers.com/'],
    ];

    /** @return array{created: int, skipped: int, errors: list<string>} */
    public function import(string $source, User $owner, bool $publish = false, ?string $snapshot = null, ?callable $progress = null): array
    {
        if (! isset(self::SOURCES[$source]) || ! $owner->canList()) {
            throw new RuntimeException('Choose a supported developer and an existing administrator or agent owner.');
        }
        $html = $snapshot ?? $this->download(self::SOURCES[$source]['url'], 2 * 1024 * 1024);
        $projects = $this->parse($source, $html);
        if ($projects === []) {
            throw new RuntimeException('No verified Indore projects found; nothing was imported.');
        }
        $result = ['created' => 0, 'skipped' => 0, 'errors' => []];
        foreach ($projects as $project) {
            $key = $source.':indore:'.Str::slug($project['title']);
            if (Property::withTrashed()->where('source_key', $key)->exists()) {
                $result['skipped']++;
                if ($progress) {
                    $progress('Already imported: '.$project['title']);
                }

                continue;
            }
            $path = null;
            try {
                $imageUrl = $project['image'];
                $sourceHost = parse_url(self::SOURCES[$source]['url'], PHP_URL_HOST);
                if (parse_url($imageUrl, PHP_URL_SCHEME) !== 'https' || parse_url($imageUrl, PHP_URL_HOST) !== $sourceHost || parse_url($imageUrl, PHP_URL_PORT) !== null || ! str_starts_with((string) parse_url($imageUrl, PHP_URL_PATH), '/wp-content/uploads/')) {
                    throw new RuntimeException('Project image must be hosted in the developer’s HTTPS uploads directory.');
                }
                $bytes = $this->download($imageUrl, 5 * 1024 * 1024);
                $info = @getimagesizefromstring($bytes);
                $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                if (! $info || ! isset($extensions[$info['mime']])) {
                    throw new RuntimeException('The project image is not a valid JPEG, PNG, or WebP.');
                }
                $path = 'property-photos/'.Str::ulid().'.'.$extensions[$info['mime']];
                if (! Storage::disk('local')->put($path, $bytes)) {
                    throw new RuntimeException('Unable to save the project image.');
                }
                DB::transaction(function () use ($source, $owner, $publish, $project, $key, $path): void {
                    $property = new Property([
                        'title' => $project['title'], 'description' => $project['description'],
                        'address' => $project['address'], 'location' => 'Indore', 'state' => 'Madhya Pradesh', 'country' => 'India',
                        'type' => $project['type'], 'purpose' => $project['purpose'], 'price' => null, 'area' => null,
                        'rooms' => null, 'bedrooms' => null, 'bathrooms' => null, 'garages' => null,
                        'availability' => 'available',
                    ]);
                    $property->user_id = $owner->id;
                    $property->reference = 'IE-'.strtoupper((string) Str::ulid());
                    $property->status = $publish ? 'published' : 'draft';
                    $property->source_key = $key;
                    $property->source_url = self::SOURCES[$source]['url'];
                    $property->source_details = array_merge($project['details'], ['developer' => self::SOURCES[$source]['name'], 'image_url' => $project['image']]);
                    $property->imported_at = now();
                    $property->save();
                    $property->photos()->create(['path' => $path, 'position' => 0]);
                });
                $result['created']++;
                if ($progress) {
                    $progress('Imported: '.$project['title']);
                }
            } catch (\Throwable $exception) {
                if ($path !== null) {
                    Storage::disk('local')->delete($path);
                }
                $result['errors'][] = $project['title'].': '.$exception->getMessage();
                if ($progress) {
                    $progress('Failed: '.$project['title']);
                }
            }
        }

        return $result;
    }

    private function download(string $url, int $limit): string
    {
        $response = Http::connectTimeout(5)->timeout(25)->withOptions([
            'allow_redirects' => false,
            'progress' => function (float $total, float $downloaded) use ($limit): void {
                if ($downloaded > $limit || $total > $limit) {
                    throw new RuntimeException('Remote file exceeds the size limit.');
                }
            },
        ])->get($url)->throw();
        if ($response->status() !== 200 || strlen($response->body()) > $limit) {
            throw new RuntimeException('Expected a bounded, successful response without redirects.');
        }

        return $response->body();
    }

    /** @return list<array<string, mixed>> */
    public function parse(string $source, string $html): array
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $xpath = new DOMXPath($document);
        $projects = [];
        if ($source === 'ruchi') {
            foreach ($xpath->query('//section[contains(concat(" ",normalize-space(@class)," ")," feature_project ")]//div[contains(concat(" ",normalize-space(@class)," ")," item ")]') as $card) {
                $title = $this->text($xpath, './/*[contains(@class,"projct_name")]', $card);
                $fields = [];
                foreach ($xpath->query('.//*[contains(concat(" ",normalize-space(@class)," ")," dtl_col ")]', $card) as $field) {
                    $pair = explode(':', trim($field->textContent), 2);
                    if (count($pair) === 2) {
                        $fields[mb_strtolower(trim($pair[0]))] = trim($pair[1]);
                    }
                }
                if ($title === '' || ! preg_match('/\bIndore\b/i', $fields['location'] ?? '')) {
                    continue;
                }
                if (! isset($fields['type'], $fields['project'], $fields['area'], $fields['status'])) {
                    throw new RuntimeException('The Ruchi project format has changed; review the source before importing.');
                }
                $projects[] = [
                    'title' => $title, 'address' => $fields['location'], 'type' => 'Land', 'purpose' => 'sale',
                    'description' => 'An Indore plotted development listed by Ruchi Realty. Published project specifications are shown below. Contact our team to confirm current plot options and pricing.',
                    'image' => $xpath->query('.//img', $card)->item(0)?->getAttribute('src') ?? '',
                    'details' => [
                        'project_type' => $fields['type'], 'development' => $fields['project'], 'project_area' => $fields['area'],
                        'project_status' => $fields['status'], 'image_caption' => $this->text($xpath, './/*[contains(@class,"artist")]', $card),
                    ],
                ];
            }
        } elseif ($source === 'emerald') {
            $verified = [
                'Emerald Nirvana' => ['type' => 'Land', 'purpose' => 'sale', 'address' => 'Jhalaria, Indore', 'project_type' => 'Luxury villa plots', 'unit_size' => '960–3500 sqft'],
                'Emerald Kingsvilla' => ['type' => 'Apartment', 'purpose' => 'sale', 'address' => 'Plot No. 06, Scheme No. 140, Sector-F, Indore', 'project_type' => 'Apartments and showrooms', 'unit_size' => '2847–3852 sqft', 'configurations' => '3 BHK / 4 BHK and showrooms'],
                'Agarwal One' => ['type' => 'Office', 'purpose' => 'rent', 'address' => '6, Scheme No. 78, Part II, Vijay Nagar, Indore', 'project_type' => 'Commercial offices · for lease only', 'unit_size' => '13000–17000 sqft'],
            ];
            foreach ($xpath->query('//section[contains(concat(" ",normalize-space(@class)," ")," em-project-listing ")]//div[contains(concat(" ",normalize-space(@class)," ")," em-projects ")]') as $card) {
                $title = $this->text($xpath, './/h3', $card);
                if (! isset($verified[$title]) || isset($projects[$title])) {
                    continue;
                }
                $project = $verified[$title];
                preg_match('/background-image:\s*url\([\'"]?(https:\/\/[^\s\)\'"]+)/i', $card->getAttribute('style'), $match);
                $projects[$title] = [
                    'title' => $title, 'address' => $project['address'], 'type' => $project['type'], 'purpose' => $project['purpose'],
                    'description' => 'An Indore project listed by Emerald Developers. Published size ranges describe project options, not an individual available unit. Contact our team for current availability and pricing.',
                    'image' => $match[1] ?? '',
                    'details' => array_diff_key($project, array_flip(['type', 'purpose', 'address'])),
                ];
            }
        } else {
            throw new RuntimeException('Unsupported developer source.');
        }

        return array_values($projects);
    }

    private function text(DOMXPath $xpath, string $query, DOMElement $context): string
    {
        return trim(preg_replace('/\s+/u', ' ', $xpath->query($query, $context)->item(0)?->textContent ?? ''));
    }
}
