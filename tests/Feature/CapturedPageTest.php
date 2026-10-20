<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CapturedPageTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_home_page_renders_dynamic_property_content(): void
    {
        $property = Property::factory()->create(['title' => 'A database powered home']);
        $this->get('/')->assertOk()->assertSee($property->title)->assertSee('/themes/homez/css/template5152.css', false)->assertDontSee('self.__next_f', false);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/dashboard.html')->assertRedirect('/dashboard');
        $this->get('/template/dashboard.html')->assertNotFound();
    }

    public function test_navigation_links_to_real_pages_instead_of_template_variants(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('aria-label="Website navigation"', false)
            ->assertSee('aria-label="Open navigation"', false)
            ->assertSee('aria-controls="site-navigation"', false)
            ->assertSee('href="'.route('properties.index').'"', false)
            ->assertSee('href="'.route('contact').'"', false)
            ->assertSee('href="'.route('properties.index', ['purpose' => 'sale']).'"', false)
            ->assertSee('href="'.route('properties.index', ['purpose' => 'rent']).'"', false)
            ->assertDontSee('Homepage 01')
            ->assertDontSee('home-02.html', false)
            ->assertDontSee('Property Half Map Grid')
            ->assertDontSee('Property Details 1')
            ->assertDontSee('Blog Default')
            ->assertDontSee('Pricing Plans')
            ->assertDontSee('topmap-grid.html', false)
            ->assertDontSee('Cookie Policy')
            ->assertDontSee('Terms Of Services');
    }

    public function test_customer_navigation_shows_account_links_without_agent_actions(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('dashboard'))->assertOk()
            ->assertSee('href="'.route('dashboard').'"', false)
            ->assertSee('href="'.route('profile.edit').'"', false)
            ->assertSee('href="'.route('conversations.index').'"', false)
            ->assertSee('action="'.route('logout').'"', false)
            ->assertDontSee('href="'.route('login').'"', false)
            ->assertDontSee('href="'.route('listings.create').'"', false)
            ->assertDontSee('href="'.route('admin.users').'"', false);
    }

    public function test_legacy_property_page_redirects_to_dynamic_listing(): void
    {
        $property = Property::factory()->create(['legacy_id' => 1]);
        $this->get('/property-details-v1/1.html')->assertRedirect(route('properties.show', $property));
    }

    public function test_unknown_and_private_captured_pages_are_not_served(): void
    {
        $this->get('/not-a-real-page.html')->assertNotFound();
        $this->get('/template/my-profile.html')->assertNotFound();
    }

    public function test_informational_page_retains_content_without_next_scripts(): void
    {
        $this->get('/about-us.html')->assertOk()->assertDontSee('self.__next_f', false);
    }
}
