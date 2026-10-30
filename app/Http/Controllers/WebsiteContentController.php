<?php

namespace App\Http\Controllers;

use App\Models\SiteContent;
use App\Models\SiteContentRevision;
use App\Models\User;
use App\Support\Cms;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class WebsiteContentController extends Controller
{
    private const REVISIONS_KEPT = 10;

    public function __construct(private Cms $cms) {}

    public function edit(string $section = 'homepage'): View
    {
        Gate::authorize('admin');
        $sections = config('cms.sections');
        abort_unless(isset($sections[$section]), 404);
        $keys = array_keys($this->sectionFields($section));

        return view('dashboard.website', [
            'sections' => $sections,
            'section' => $section,
            'groups' => $sections[$section]['groups'],
            'undoable' => SiteContentRevision::whereIn('key', $keys)->distinct()->pluck('key')->flip()->all(),
        ]);
    }

    public function update(Request $request, string $section): RedirectResponse
    {
        Gate::authorize('admin');
        abort_unless(isset(config('cms.sections')[$section]), 404);
        $fields = $this->sectionFields($section);
        [$rules, $attributes] = $this->rules($fields);
        $data = $request->validate($rules, [], $attributes);

        DB::transaction(function () use ($request, $fields, $data): void {
            foreach ($fields as $key => $field) {
                $id = self::inputId($key);
                $value = match ($field['type']) {
                    'image' => $request->hasFile("images.$id") ? $request->file("images.$id")->store('cms', 'public') : null,
                    'faq' => json_encode(array_values(array_filter(
                        array_map(fn (array $item): array => ['question' => trim((string) ($item['question'] ?? '')), 'answer' => trim((string) ($item['answer'] ?? ''))], $data['faq'][$id] ?? []),
                        fn (array $item): bool => $item['question'] !== '' || $item['answer'] !== '',
                    )), JSON_UNESCAPED_UNICODE),
                    default => (string) ($data['content'][$id] ?? ''),
                };
                if ($value !== null) {
                    $this->store($key, $field, $value, $request->user());
                }
            }
        });
        $this->cms->forget();

        return back()->with('status', 'Website content saved. Changes are live on the site now.');
    }

    public function reset(Request $request, string $key): RedirectResponse
    {
        Gate::authorize('admin');
        abort_unless($this->cms->has($key), 404);
        $field = $this->cms->field($key);
        $this->store($key, $field, $this->encode($field, $field['default']), $request->user());
        $this->cms->forget();

        return back()->with('status', '“'.$field['label'].'” was reset to the original content.');
    }

    public function undo(string $key): RedirectResponse
    {
        Gate::authorize('admin');
        abort_unless($this->cms->has($key), 404);
        $revision = SiteContentRevision::where('key', $key)->latest('id')->firstOrFail();
        DB::transaction(function () use ($key, $revision): void {
            if ($revision->was_default) {
                SiteContent::where('key', $key)->delete();
            } else {
                SiteContent::updateOrCreate(['key' => $key], ['value' => $revision->value]);
            }
            $revision->delete();
        });
        $this->cms->forget();

        return back()->with('status', 'The last change to “'.$this->cms->field($key)['label'].'” was undone.');
    }

    public static function inputId(string $key): string
    {
        return str_replace('.', '__', $key);
    }

    /** @return array<string,array> */
    private function sectionFields(string $section): array
    {
        return array_filter($this->cms->fields(), fn (array $field): bool => $field['section'] === $section);
    }

    /** @return array{0:array<string,array>,1:array<string,string>} */
    private function rules(array $fields): array
    {
        $rules = [];
        $attributes = [];
        foreach ($fields as $key => $field) {
            $id = self::inputId($key);
            $presence = $field['required'] ? 'required' : 'nullable';
            if ($field['type'] === 'image') {
                $rules["images.$id"] = ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
                $attributes["images.$id"] = $field['label'];

                continue;
            }
            if ($field['type'] === 'faq') {
                $rules["faq.$id"] = ['nullable', 'array', 'max:'.($field['max'] ?? 20)];
                $rules["faq.$id.*"] = ['array:question,answer'];
                $rules["faq.$id.*.question"] = ['nullable', 'string', 'max:200', "required_with:faq.$id.*.answer"];
                $rules["faq.$id.*.answer"] = ['nullable', 'string', 'max:2000', "required_with:faq.$id.*.question"];
                $attributes["faq.$id.*.question"] = 'question';
                $attributes["faq.$id.*.answer"] = 'answer';

                continue;
            }
            $rules["content.$id"] = match ($field['type']) {
                'url' => [$presence, 'string', 'max:500', 'url:http,https'],
                'email' => [$presence, 'email', 'max:255'],
                'tel' => [$presence, 'string', 'max:30', 'regex:/^[0-9+()\-\s]*$/'],
                'markdown' => [$presence, 'string', 'max:'.($field['max'] ?? 20000)],
                'textarea' => [$presence, 'string', 'max:'.($field['max'] ?? 1000)],
                default => [$presence, 'string', 'max:'.($field['max'] ?? 200)],
            };
            $attributes["content.$id"] = $field['label'];
        }

        return [$rules, $attributes];
    }

    private function encode(array $field, mixed $value): string
    {
        return $field['type'] === 'faq' ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value;
    }

    /** Save one value, keeping the previous one as a revision. Values equal to the default are stored as "no override". */
    private function store(string $key, array $field, string $value, ?User $user): void
    {
        $current = SiteContent::where('key', $key)->first();
        $currentValue = $current?->value ?? $this->encode($field, $field['default']);
        if ($value === $currentValue) {
            return;
        }
        SiteContentRevision::create(['key' => $key, 'value' => $current?->value, 'was_default' => $current === null, 'user_id' => $user?->id]);
        $stale = SiteContentRevision::where('key', $key)->latest('id')->skip(self::REVISIONS_KEPT)->take(PHP_INT_MAX)->pluck('id');
        SiteContentRevision::whereIn('id', $stale)->delete();

        if ($value === $this->encode($field, $field['default'])) {
            $current?->delete();
        } else {
            SiteContent::updateOrCreate(['key' => $key], ['value' => $value, 'updated_by' => $user?->id]);
        }
    }
}
