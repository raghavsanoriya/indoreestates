<?php

namespace Tests\Feature;

use App\Http\Controllers\WebsiteContentController;
use App\Models\SiteContent;
use App\Models\User;
use App\Support\Cms;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebsiteCmsTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /** The form exactly as it loads for a section: every field at its current default. */
    private function form(string $section, array $content = [], array $extra = []): array
    {
        $payload = ['content' => [], 'faq' => []];
        foreach (app(Cms::class)->fields() as $key => $field) {
            if ($field['section'] !== $section || $field['type'] === 'image') {
                continue;
            }
            $id = WebsiteContentController::inputId($key);
            if ($field['type'] === 'faq') {
                $payload['faq'][$id] = $field['default'];
            } else {
                $payload['content'][$id] = $content[$key] ?? $field['default'];
            }
        }

        return array_replace_recursive($payload, $extra);
    }

    public function test_public_pages_show_the_original_content_until_edited(): void
    {
        $this->get('/')->assertOk()->assertSee('Easy Way to Find a Perfect Property')->assertSee('<title>indoreestate · Properties in Indore</title>', false)->assertDontSee('round-text.png');
        $this->get('/about-us.html')->assertOk()->assertSee('<h2>Your Address in Indore</h2>', false)->assertSee('About indoreestate');
        $this->get('/faq.html')->assertOk()->assertSee('Where are properties located?')->assertSee('Are payments and bookings active?');
        $this->get('/contact')->assertOk()->assertSee('Get in Touch')->assertSee('Madhya Pradesh, India');
        $this->assertSame(0, SiteContent::count());
    }

    public function test_only_admins_can_open_or_change_website_content(): void
    {
        $this->get('/dashboard/website')->assertRedirect('/login');
        foreach (['customer', 'agent'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get('/dashboard/website')->assertForbidden();
            $this->actingAs($user)->put('/dashboard/website/homepage', $this->form('homepage', ['home.hero.title' => 'Hijacked']))->assertForbidden();
            $this->actingAs($user)->delete('/dashboard/website/content/home.hero.title')->assertForbidden();
        }
        $this->assertSame(0, SiteContent::count());

        $this->actingAs($this->admin())->get('/dashboard/website')->assertOk()->assertSee('Website Content')->assertSee('Edit what visitors see');
        $this->get('/dashboard/website/seo')->assertOk()->assertSee('Default page title');
        $this->get('/dashboard/website/unknown')->assertNotFound();
    }

    public function test_admin_edits_homepage_text_and_it_goes_live(): void
    {
        $this->actingAs($this->admin())
            ->put('/dashboard/website/homepage', $this->form('homepage', ['home.hero.title' => 'Find Your Home in Indore', 'home.cta.button' => 'Start Searching']))
            ->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame(2, SiteContent::count());
        $this->get('/')->assertSee('Find Your Home in Indore')->assertSee('Start Searching')->assertDontSee('Easy Way to Find a Perfect Property');
        $this->get('/dashboard/website')->assertSee('Edited')->assertSee('Reset to original')->assertSee('Undo');
    }

    public function test_required_fields_and_lengths_are_enforced(): void
    {
        $this->actingAs($this->admin())->put('/dashboard/website/homepage', $this->form('homepage', ['home.hero.title' => '', 'home.cta.button' => str_repeat('x', 41)]))
            ->assertSessionHasErrors(['content.home__hero__title', 'content.home__cta__button']);
        $this->assertSame(0, SiteContent::count());
    }

    public function test_rich_text_cannot_inject_html_or_unsafe_links(): void
    {
        $body = "## Welcome\n\n**Trusted** advice.\n\n<script>alert('x')</script><img src=x onerror=alert(1)>\n\n[click](javascript:alert(1)) [site](https://indoreestates.com)";
        $this->actingAs($this->admin())->put('/dashboard/website/pages', $this->form('pages', ['pages.about.body' => $body]))->assertSessionHasNoErrors();

        $this->get('/about-us.html')->assertOk()
            ->assertSee('<h2>Welcome</h2>', false)->assertSee('<strong>Trusted</strong>', false)->assertSee('<a href="https://indoreestates.com">site</a>', false)
            ->assertDontSee("<script>alert('x')</script>", false)->assertDontSee('onerror', false)->assertDontSee('javascript:', false);
    }

    public function test_reset_and_undo_walk_back_through_changes(): void
    {
        $this->actingAs($this->admin());
        $this->put('/dashboard/website/homepage', $this->form('homepage', ['home.hero.title' => 'First edit']));
        $this->put('/dashboard/website/homepage', $this->form('homepage', ['home.hero.title' => 'Second edit']));
        $this->get('/')->assertSee('Second edit');

        $this->delete('/dashboard/website/content/home.hero.title')->assertRedirect();
        $this->get('/')->assertSee('Easy Way to Find a Perfect Property');

        $this->post('/dashboard/website/content/home.hero.title/undo');
        $this->get('/')->assertSee('Second edit');
        $this->post('/dashboard/website/content/home.hero.title/undo');
        $this->get('/')->assertSee('First edit');
        $this->post('/dashboard/website/content/home.hero.title/undo');
        $this->get('/')->assertSee('Easy Way to Find a Perfect Property');
        $this->post('/dashboard/website/content/home.hero.title/undo')->assertNotFound();
        $this->delete('/dashboard/website/content/not.a.key')->assertNotFound();
    }

    public function test_images_can_be_replaced_but_only_with_real_photos(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());

        $this->put('/dashboard/website/homepage', $this->form('homepage', extra: ['images' => ['home__hero__image' => UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml')]]))
            ->assertSessionHasErrors('images.home__hero__image');

        $this->put('/dashboard/website/homepage', $this->form('homepage', extra: ['images' => ['home__hero__image' => UploadedFile::fake()->image('hero.jpg', 1600, 900)]]))
            ->assertSessionHasNoErrors();
        $path = SiteContent::where('key', 'home.hero.image')->value('value');
        Storage::disk('public')->assertExists($path);
        $this->get('/')->assertSee('background-image:url(', false)->assertSee(basename($path));
    }

    public function test_faq_questions_can_be_added_edited_and_removed(): void
    {
        $this->actingAs($this->admin());
        $faq = ['faq__items' => [
            ['question' => 'Do you charge buyers?', 'answer' => 'No, browsing and enquiries are free.'],
            ['question' => '', 'answer' => ''],
            ['question' => 'Which areas do you cover?', 'answer' => 'All of Indore.'],
        ]];
        $this->put('/dashboard/website/faq', ['content' => ['pages__faq__title' => 'Questions & Answers'], 'faq' => $faq])->assertSessionHasNoErrors();

        $this->get('/faq.html')->assertSee('Questions &amp; Answers', false)->assertSee('Do you charge buyers?')->assertSee('All of Indore.')->assertDontSee('Where are properties located?');
        $this->assertCount(2, app(Cms::class)->faq());

        $this->put('/dashboard/website/faq', ['content' => ['pages__faq__title' => 'FAQ'], 'faq' => ['faq__items' => [['question' => 'Missing answer?', 'answer' => '']]]])
            ->assertSessionHasErrors('faq.faq__items.0.answer');
    }

    public function test_contact_details_and_social_links_show_once_filled_in(): void
    {
        $this->actingAs($this->admin());
        $this->get('/')->assertDontSee('wa.me')->assertDontSee('instagram.com');

        $this->put('/dashboard/website/contact', $this->form('contact', ['social.instagram' => 'javascript:alert(1)']))->assertSessionHasErrors('content.social__instagram');
        $this->put('/dashboard/website/contact', $this->form('contact', ['contact.phone' => 'call me <b>now</b>']))->assertSessionHasErrors('content.contact__phone');

        $this->put('/dashboard/website/contact', $this->form('contact', [
            'contact.phone' => '+91 98765 43210', 'contact.whatsapp' => '+91 98765 43210', 'contact.email' => 'hello@indoreestates.com',
            'social.instagram' => 'https://instagram.com/indoreestates',
        ]))->assertSessionHasNoErrors();
        $this->get('/')->assertSee('tel:+919876543210', false)->assertSee('https://wa.me/919876543210', false)->assertSee('mailto:hello@indoreestates.com', false)->assertSee('https://instagram.com/indoreestates', false);
        $this->get('/contact')->assertSee('hello@indoreestates.com');
    }

    public function test_seo_titles_and_descriptions_can_be_set_per_page(): void
    {
        $this->actingAs($this->admin())->put('/dashboard/website/seo', $this->form('seo', [
            'seo.home.title' => 'Indore Properties for Sale & Rent | indoreestate',
            'seo.home.description' => 'Flats, villas and plots in Indore.',
        ]))->assertSessionHasNoErrors();

        $this->get('/')->assertSee('<title>Indore Properties for Sale &amp; Rent | indoreestate</title>', false)->assertSee('<meta name="description" content="Flats, villas and plots in Indore.">', false);
        $this->get('/contact')->assertSee('<title>indoreestate · Properties in Indore</title>', false)->assertSee('content="Explore properties and developer projects in Indore with indoreestate."', false);
    }

    public function test_saving_an_unchanged_form_stores_nothing(): void
    {
        $this->actingAs($this->admin());
        foreach (array_keys(config('cms.sections')) as $section) {
            $this->put("/dashboard/website/$section", $this->form($section))->assertSessionHasNoErrors();
        }
        $this->assertSame(0, SiteContent::count());
    }
}
