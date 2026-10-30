<?php

namespace App\Support;

use App\Models\SiteContent;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Cms
{
    public const CACHE_KEY = 'cms.site_contents';

    private ?array $overrides = null;

    /** @return array<string,array> Every editable field keyed by content key, with its section and group. */
    public function fields(): array
    {
        $fields = [];
        foreach (config('cms.sections') as $section => $definition) {
            foreach ($definition['groups'] as $group => $groupFields) {
                foreach ($groupFields as $key => $field) {
                    $fields[$key] = $field + ['section' => $section, 'group' => $group, 'required' => false];
                }
            }
        }

        return $fields;
    }

    public function field(string $key): array
    {
        return $this->fields()[$key] ?? throw new InvalidArgumentException("Unknown website content key [$key].");
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->fields());
    }

    /** @return array<string,string|null> Saved values, keyed by content key. */
    public function overrides(): array
    {
        if ($this->overrides !== null) {
            return $this->overrides;
        }
        try {
            return $this->overrides = Cache::rememberForever(self::CACHE_KEY, fn () => SiteContent::pluck('value', 'key')->all());
        } catch (QueryException) {
            // The table may not exist yet mid-deploy: show the default content rather than an error page.
            return [];
        }
    }

    public function isCustom(string $key): bool
    {
        return array_key_exists($key, $this->overrides());
    }

    public function value(string $key): mixed
    {
        $field = $this->field($key);
        if (! $this->isCustom($key)) {
            return $field['default'];
        }
        $value = $this->overrides()[$key];

        return $field['type'] === 'faq' ? (json_decode((string) $value, true) ?: []) : (string) $value;
    }

    public function text(string $key): string
    {
        return (string) $this->value($key);
    }

    /** Rich text as HTML. Raw HTML is stripped and unsafe link schemes dropped, so editors cannot inject scripts. */
    public function html(string $key): HtmlString
    {
        return new HtmlString(Str::markdown($this->text($key), ['html_input' => 'strip', 'allow_unsafe_links' => false]));
    }

    public function image(string $key): string
    {
        return $this->isCustom($key) ? Storage::disk('public')->url($this->text($key)) : asset($this->field($key)['default']);
    }

    /** @return array<int,array{question:string,answer:string}> */
    public function faq(): array
    {
        return $this->value('faq.items');
    }

    public function pageTitle(string $page): string
    {
        return $this->text("seo.$page.title") ?: $this->text('seo.default.title');
    }

    public function pageDescription(string $page): string
    {
        return $this->text("seo.$page.description") ?: $this->text('seo.default.description');
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->overrides = null;
    }
}
