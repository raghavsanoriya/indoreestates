<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use App\Services\DeveloperProjectImporter;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeveloperPropertyImportTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function owner(): User
    {
        $owner = User::factory()->create();
        $owner->role = 'agent';
        $owner->save();

        return $owner;
    }

    private function source(string $name = 'ruchi'): string
    {
        return file_get_contents(base_path('tests/Fixtures/'.$name.'-projects.html'));
    }

    private function fakeImages(string $source = 'ruchi'): void
    {
        Storage::fake('local');
        Http::preventStrayRequests();
        $host = $source === 'ruchi' ? 'ruchirealty.com' : 'emeralddevelopers.com';
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j7ioAAAAASUVORK5CYII=');
        Http::fake([
            'https://'.$host.'/' => Http::response($this->source($source)),
            'https://'.$host.'/wp-content/uploads/test.png' => Http::response($png, 200, ['Content-Type' => 'image/png']),
        ]);
    }

    public function test_ruchi_import_publishes_only_indore_with_real_photos_and_unknown_prices(): void
    {
        $this->fakeImages();
        $owner = $this->owner();

        $result = app(DeveloperProjectImporter::class)->import('ruchi', $owner, true);

        $this->assertSame(1, $result['created']);
        $this->assertSame([], $result['errors']);
        $this->assertDatabaseCount('properties', 1);
        $property = Property::firstOrFail();
        $this->assertSame('Indore', $property->location);
        $this->assertNull($property->price);
        $this->assertNull($property->area);
        $this->assertNull($property->bedrooms);
        $this->assertSame('8 Acres, 129 Plots', $property->source_details['project_area']);
        $this->assertSame($owner->id, $property->user_id);
        Storage::disk('local')->assertExists($property->photos->first()->path);
        Http::assertSentCount(2);
        $this->get(route('properties.show', $property))->assertOk()->assertSee('Contact us')->assertSee('Price on request')->assertSee('Ruchi Realty')->assertDontSee('0 sqft')->assertDontSee('untrusted()');
        $this->get(route('media.photo', $property->photos->first()))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->actingAs($owner)->get(route('listings.index'))->assertOk()->assertSee('Oscar Pride');
    }

    public function test_repeat_import_keeps_local_edits_and_does_not_restore_deleted_records(): void
    {
        $this->fakeImages();
        $owner = $this->owner();
        $importer = app(DeveloperProjectImporter::class);
        $importer->import('ruchi', $owner, true, $this->source());
        $property = Property::firstOrFail();
        $property->title = 'Locally edited title';
        $property->save();

        $result = $importer->import('ruchi', $owner, true, $this->source());

        $this->assertSame(1, $result['skipped']);
        $this->assertSame('Locally edited title', $property->fresh()->title);
        $this->assertDatabaseCount('property_photos', 1);
        $property->delete();
        $importer->import('ruchi', $owner, true, $this->source());
        $this->assertSoftDeleted($property);
        Http::assertSentCount(1);
    }

    public function test_emerald_import_deduplicates_slides_and_preserves_lease_only_project(): void
    {
        $this->fakeImages('emerald');

        $result = app(DeveloperProjectImporter::class)->import('emerald', $this->owner(), true);

        $this->assertSame(3, $result['created']);
        $this->assertDatabaseCount('properties', 3);
        $this->assertDatabaseHas('properties', ['title' => 'Agarwal One', 'purpose' => 'rent', 'type' => 'Office', 'price' => null]);
        $this->assertSame('2847–3852 sqft', Property::where('title', 'Emerald Kingsvilla')->firstOrFail()->source_details['unit_size']);
        Http::assertSentCount(4);
    }

    public function test_failed_or_untrusted_images_do_not_leave_partial_listings(): void
    {
        Storage::fake('local');
        Http::preventStrayRequests();
        Http::fake(['https://ruchirealty.com/wp-content/uploads/test.png' => Http::response('not an image')]);
        $owner = $this->owner();
        $importer = app(DeveloperProjectImporter::class);

        $badImage = $importer->import('ruchi', $owner, true, $this->source());
        $badHost = $importer->import('ruchi', $owner, true, str_replace('https://ruchirealty.com/wp-content/uploads/test.png', 'https://127.0.0.1/private.png', $this->source()));

        $this->assertCount(1, $badImage['errors']);
        $this->assertCount(1, $badHost['errors']);
        $this->assertDatabaseCount('properties', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
        Http::assertSentCount(1);
    }

    public function test_public_search_and_direct_urls_hide_out_of_city_listings_without_deleting_them(): void
    {
        $indore = Property::factory()->create(['title' => 'Indore public home']);
        $outside = Property::factory()->create(['title' => 'Outside city home', 'location' => 'Pune']);

        $this->get('/properties')->assertOk()->assertSee($indore->title)->assertDontSee($outside->title);
        $this->get('/')->assertOk()->assertSee($indore->title)->assertDontSee($outside->title);
        $this->get(route('properties.show', $outside))->assertNotFound();
        $this->assertModelExists($outside);
    }

    public function test_administrator_cannot_publish_non_indore_property(): void
    {
        $admin = $this->owner();
        $admin->role = 'admin';
        $admin->save();
        $outside = Property::factory()->create(['location' => 'Bhopal', 'status' => 'pending']);

        $this->actingAs($admin)->patch(route('listings.moderate', $outside), ['decision' => 'published'])->assertSessionHasErrors('location');

        $this->assertSame('pending', $outside->fresh()->status);
    }

    public function test_contact_button_prefills_property_context_but_not_private_listing_details(): void
    {
        $public = Property::factory()->create(['price' => null, 'title' => 'Public project']);
        $private = Property::factory()->create(['status' => 'draft', 'title' => 'Secret private project']);

        $this->get(route('contact', ['property' => $public->id]))->assertOk()->assertSee('current pricing and availability for Public project');
        $this->get(route('contact', ['property' => $private->id]))->assertOk()->assertDontSee('Secret private project');
    }

    public function test_agent_can_save_imported_unknown_measurements_without_forging_source_fields(): void
    {
        $this->fakeImages();
        $owner = $this->owner();
        app(DeveloperProjectImporter::class)->import('ruchi', $owner, true, $this->source());
        $property = Property::firstOrFail();

        $this->actingAs($owner)->put(route('listings.update', $property), [
            'title' => $property->title, 'description' => $property->description, 'address' => $property->address,
            'location' => 'Indore', 'country' => 'India', 'type' => 'Land', 'purpose' => 'sale', 'price' => null, 'area' => null,
            'rooms' => null, 'bedrooms' => null, 'bathrooms' => null, 'garages' => null, 'availability' => 'available', 'intent' => 'submit',
            'source_url' => 'https://evil.test',
        ])->assertSessionHasNoErrors();

        $this->assertSame('pending', $property->fresh()->status);
        $this->assertNull($property->fresh()->price);
        $this->assertSame('https://ruchirealty.com/', $property->fresh()->source_url);
    }

    public function test_agent_cannot_submit_a_property_outside_indore(): void
    {
        $owner = $this->owner();
        $property = Property::factory()->for($owner, 'owner')->create(['status' => 'draft']);

        $this->actingAs($owner)->put(route('listings.update', $property), [
            'title' => $property->title, 'description' => $property->description, 'address' => $property->address,
            'location' => 'Pune', 'country' => 'India', 'type' => 'Land', 'purpose' => 'sale', 'price' => 5000000, 'area' => 1000,
            'rooms' => 0, 'bedrooms' => 0, 'bathrooms' => 0, 'garages' => 0, 'availability' => 'available', 'intent' => 'submit',
        ])->assertSessionHasErrors('location');

        $this->assertSame('draft', $property->fresh()->status);
    }
}
