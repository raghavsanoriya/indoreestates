<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use App\Services\DemoContentCleaner;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DemoCleanupTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_cleanup_removes_seeded_and_explicit_qa_records_but_preserves_sourced_and_user_records(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $customer = User::factory()->create();
        $sample = Property::factory()->for($agent, 'owner')->create(['is_demo' => true]);
        $trashed = Property::factory()->for($agent, 'owner')->create(['is_demo' => true]);
        $trashed->delete();
        $qa = Property::factory()->for($agent, 'owner')->create(['status' => 'draft']);
        $imported = Property::factory()->for($agent, 'owner')->create(['title' => 'Imported Indore project', 'source_key' => 'ruchi:indore:project', 'source_details' => ['developer' => 'Ruchi Realty'], 'price' => null]);
        $userListing = Property::factory()->for($agent, 'owner')->create(['title' => 'Customer entered property']);
        $sample->photos()->create(['path' => 'images/home/house-1.jpg', 'is_demo' => true, 'position' => 0]);
        $sample->views()->create(['day' => today()->toDateString(), 'views' => 8]);
        $customer->favorites()->attach([$sample->id, $imported->id]);
        $conversation = Conversation::create(['property_id' => $sample->id, 'customer_id' => $customer->id, 'agent_id' => $agent->id]);
        $conversation->messages()->create(['user_id' => $customer->id, 'body' => 'Sample inquiry']);
        Review::forceCreate(['property_id' => $sample->id, 'user_id' => $customer->id, 'rating' => 5, 'body' => 'Sample review', 'status' => 'approved']);
        $realConversation = Conversation::create(['property_id' => $imported->id, 'customer_id' => $customer->id, 'agent_id' => $agent->id]);
        $realMessage = $realConversation->messages()->create(['user_id' => $customer->id, 'body' => 'Please confirm the current price.']);

        $removed = app(DemoContentCleaner::class)->clean([$qa->id, $imported->id]);

        $this->assertSame(3, $removed);
        foreach ([$sample, $trashed, $qa] as $property) {
            $this->assertDatabaseMissing('properties', ['id' => $property->id]);
        }
        $this->assertModelExists($imported);
        $this->assertModelExists($userListing);
        $this->assertSame('Imported Indore project', $imported->fresh()->title);
        $this->assertNull($imported->fresh()->price);
        $this->assertDatabaseMissing('conversations', ['id' => $conversation->id]);
        $this->assertDatabaseMissing('messages', ['conversation_id' => $conversation->id]);
        $this->assertDatabaseCount('reviews', 0);
        $this->assertDatabaseCount('property_views', 0);
        $this->assertDatabaseCount('property_photos', 0);
        $this->assertDatabaseCount('favorites', 1);
        $this->assertModelExists($realMessage);
        $this->assertDatabaseCount('users', 2);
        $this->assertSame(0, app(DemoContentCleaner::class)->clean());
        $this->actingAs($agent)->get(route('dashboard'))->assertOk()->assertSee('Imported Indore project')->assertDontSee('Sample inquiry');
    }

    public function test_cleanup_command_defaults_to_preview_and_rejects_invalid_ids_without_changes(): void
    {
        $property = Property::factory()->create(['is_demo' => true]);

        $this->artisan('site:clean-demo')->expectsOutput('Preview only: 1 listings selected. No data changed.')->assertExitCode(0);
        $this->artisan('site:clean-demo', ['--property' => ['invalid']])->assertExitCode(1);

        $this->assertModelExists($property);
    }

    public function test_cleanup_refuses_to_apply_without_a_recoverable_file_database(): void
    {
        $property = Property::factory()->create(['is_demo' => true]);

        $this->artisan('site:clean-demo', ['--apply' => true])->expectsOutput('Cleanup requires a file-backed SQLite database so a recoverable backup can be made.')->assertExitCode(1);

        $this->assertModelExists($property);
    }

    public function test_empty_cleanup_is_repeatable(): void
    {
        $this->artisan('site:clean-demo', ['--apply' => true])->expectsOutput('No sample listings remain.')->assertExitCode(0);

        $this->assertDatabaseCount('properties', 0);
    }

    public function test_public_pages_do_not_serve_template_articles_partners_people_or_pricing_plans(): void
    {
        foreach (['/', '/about-us.html', '/our-service.html', '/faq.html', '/blog.html', '/blog-grid.html', '/pricing.html', '/privacy-policy.html'] as $url) {
            $this->get($url)->assertOk()
                ->assertDontSee('Jerome Bell')
                ->assertDontSee('Demo Agent')
                ->assertDontSee('Lorem ipsum')
                ->assertDontSee('150+')
                ->assertDontSee('Building gains into housing stocks')
                ->assertDontSee('template/images/agents/', false)
                ->assertDontSee('href="'.url('/blog.html').'"', false);
        }
        $this->get('/blog-detail/1.html')->assertNotFound();
        $this->get('/blog.html')->assertOk()->assertSee('No articles published yet');
        $this->get('/faq.html')->assertOk()->assertSee('Indore only');
        $this->get('/pricing.html')->assertOk()->assertSee('paid subscription plans are not available');
    }

    public function test_properties_without_photos_do_not_fall_back_to_a_fake_house(): void
    {
        $property = Property::factory()->create();

        $this->assertSame(asset('branding/property-placeholder.svg'), $property->coverUrl());
        $this->get('/')->assertOk()->assertSee('branding/property-placeholder.svg', false)->assertDontSee('template/images/home/house-1.jpg', false);
    }
}
