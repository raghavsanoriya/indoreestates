<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class HomeTwoTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_homepage_uses_home_two_with_real_indore_records_and_no_demo_endorsements(): void
    {
        Property::factory()->create(['title' => 'Indore apartment for rent', 'purpose' => 'rent']);
        Property::factory()->create(['title' => 'Indore land for sale', 'purpose' => 'sale', 'type' => 'Land']);
        Property::factory()->create(['title' => 'Outside city listing', 'location' => 'Bhopal']);
        Property::factory()->create(['title' => 'Private unpublished listing', 'status' => 'pending']);

        $this->get('/')->assertOk()
            ->assertSee('class="home-banner-two"', false)
            ->assertSee('class="main-header-two"', false)
            ->assertSee('class="footer-two"', false)
            ->assertSee('Find your <span>Next Home</span> Away from Home.', false)
            ->assertSee('Indore apartment for rent')
            ->assertSee('Indore land for sale')
            ->assertSee('2 Indore Properties')
            ->assertDontSee('Outside city listing')
            ->assertDontSee('Private unpublished listing')
            ->assertDontSee('Ratings 5.0')
            ->assertDontSee('Lily Brooks')
            ->assertDontSee('New York')
            ->assertDontSee('12000')
            ->assertDontSee('class="Home-banner-section"', false);
    }

    public function test_home_variants_redirect_to_the_home_two_homepage(): void
    {
        foreach (['index.html', 'index-2.html', 'index-3.html'] as $page) {
            $this->get('/'.$page)->assertRedirect('/');
        }
        $this->get('/assets/index-2.html')->assertNotFound();
        $this->get('/storage/app/template-pages/index-2.html')->assertNotFound();
    }

    public function test_non_home_pages_render_the_standard_footer_instead_of_blade_source(): void
    {
        $this->get('/properties')->assertOk()
            ->assertSee('footer-dark', false)
            ->assertDontSee("@include('partials.original-footer')", false);

        $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk()
            ->assertSee('footer-dark', false)
            ->assertDontSee("@include('partials.original-footer')", false);
    }

    public function test_home_two_cards_and_search_ratings_include_only_approved_reviews(): void
    {
        $property = Property::factory()->create(['purpose' => 'sale']);
        foreach ([['approved', 4], ['pending', 1], ['rejected', 2]] as [$status, $rating]) {
            Review::forceCreate(['property_id' => $property->id, 'user_id' => User::factory()->create()->id, 'rating' => $rating, 'body' => 'Review in '.$status.' state', 'status' => $status]);
        }

        $this->get('/')->assertOk()->assertSee('4.0 (1)')->assertDontSee('Review in pending state')->assertDontSee('Review in rejected state');
        $this->get('/properties')->assertOk()->assertSee('4.0 (1 Reviews)')->assertDontSee('2.3 (3 Reviews)');
    }

    public function test_location_search_matches_indore_street_addresses_without_exposing_outside_cities(): void
    {
        Property::factory()->create(['title' => 'Office in Vijay Nagar', 'address' => 'Scheme 78, Vijay Nagar', 'type' => 'Office']);
        Property::factory()->create(['title' => 'Unrelated address', 'address' => 'Rau']);
        Property::factory()->create(['title' => 'Other city with same street', 'address' => 'Vijay Nagar', 'location' => 'Bhopal']);

        $this->get('/properties?location=Vijay+Nagar')->assertOk()->assertSee('Office in Vijay Nagar')->assertDontSee('Unrelated address')->assertDontSee('Other city with same street');
    }

    public function test_unknown_pricing_is_not_invented_and_contact_context_is_available(): void
    {
        $property = Property::factory()->create(['title' => 'Price enquiry project', 'price' => null, 'purpose' => 'sale']);

        $this->get('/')->assertOk()->assertSee('Price on request')->assertSee(route('contact', ['property' => $property->id]), false);
        $this->get(route('properties.show', $property))->assertOk()->assertSee('Contact us')->assertSee('Price on request');
        $this->assertNull($property->fresh()->price);
    }

    public function test_public_property_text_is_escaped_in_home_two_and_search(): void
    {
        $property = Property::factory()->create(['title' => '<script>unsafeTitle()</script>', 'purpose' => 'sale']);

        $this->get('/')->assertOk()->assertSee('&lt;script&gt;unsafeTitle()&lt;/script&gt;', false)->assertDontSee($property->title, false);
        $this->get('/properties')->assertOk()->assertDontSee($property->title, false);
    }
}
