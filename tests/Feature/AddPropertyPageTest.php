<?php

namespace Tests\Feature;

use App\Models\Amenity;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AddPropertyPageTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function agent(): User
    {
        $user = User::factory()->create();
        $user->role = 'agent';
        $user->save();

        return $user;
    }

    private function image(string $name = 'home.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j7ioAAAAASUVORK5CYII='));
    }

    private function payload(array $changes = []): array
    {
        return array_replace([
            'title' => 'Add Property QA home',
            'description' => 'A spacious home with a private garden.',
            'address' => '12 Sample Lane', 'location' => 'Indore', 'country' => 'India', 'state' => 'Madhya Pradesh',
            'type' => 'House', 'purpose' => 'sale', 'price' => 7500000, 'area' => 1800,
            'rooms' => 5, 'bedrooms' => 3, 'bathrooms' => 2, 'garages' => 1,
            'availability' => 'available', 'intent' => 'draft',
        ], $changes);
    }

    public function test_agent_sees_the_supplied_html_sections_in_dashboard(): void
    {
        $agent = $this->agent();
        Amenity::create(['name' => 'Garden']);

        $this->actingAs($agent)->get(route('listings.create'))
            ->assertOk()
            ->assertSeeInOrder(['Property Information', 'Property Details', 'Amenities', 'Gallery', 'Videos', 'Description', 'Floor Plans', 'Location', 'Virtual Tour', 'Agent Contact'])
            ->assertSee('Garden')
            ->assertSee('Save Draft')
            ->assertSee('Save & Preview', false)
            ->assertDontSee('Listing <span>', false)
            ->assertDontSee('Pages <span>', false)
            ->assertSee('href="'.route('contact').'"', false)
            ->assertSee('multipart/form-data', false);
    }

    public function test_guests_and_customers_cannot_open_or_submit_add_property(): void
    {
        $this->get(route('listings.create'))->assertRedirect(route('login'));
        $this->post(route('listings.store'), $this->payload())->assertRedirect(route('login'));
        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('listings.create'))->assertForbidden();
        $this->post(route('listings.store'), $this->payload())->assertForbidden();
        $this->assertDatabaseCount('properties', 0);
    }

    public function test_draft_persists_all_optional_sections_and_images(): void
    {
        Storage::fake('local');
        $agent = $this->agent();
        $amenity = Amenity::create(['name' => 'Garden']);
        $data = $this->payload([
            'photos' => [$this->image()],
            'amenities' => [$amenity->id],
            'presentation' => [
                'unit_price' => 'total', 'before_price_label' => 'From', 'after_price_label' => 'Negotiable', 'label' => 'Featured',
                'tour_mode' => 'image', 'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
                'floors_enabled' => '1', 'floors' => [['name' => 'Ground floor', 'price' => 3000000, 'size' => 900, 'size_postfix' => 'sqft', 'bedrooms' => 2, 'bathrooms' => 1, 'description' => 'Spacious ground floor']],
                'contact_mode' => 'other', 'contact_name' => 'Sample contact', 'contact_email' => 'contact@example.test', 'contact_phone' => '9876543210',
            ],
            'presentation_files' => ['tour' => $this->image('panorama.png'), 'floors' => [$this->image('plan.png')]],
        ]);

        $this->actingAs($agent)->post(route('listings.store'), $data)->assertRedirect(route('listings.index'))->assertSessionHasNoErrors();

        $property = Property::firstOrFail();
        $this->assertSame('draft', $property->status);
        $this->assertSame($agent->id, $property->user_id);
        $this->assertSame('Ground floor', data_get($property->presentation, 'floors.0.name'));
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $property->presentation['video_url']);
        $this->assertSame('contact@example.test', $property->presentation['contact_email']);
        $this->assertSame('From ₹75,00,000 Negotiable', $property->priceLabel());
        $this->assertDatabaseHas('amenity_property', ['property_id' => $property->id, 'amenity_id' => $amenity->id]);
        Storage::disk('local')->assertExists([
            $property->photos->first()->path,
            $property->presentation['tour_image'],
            $property->presentation['floors'][0]['image'],
        ]);
        $this->get(route('listings.preview', $property))->assertOk()->assertSee('Ground floor')->assertSee('Sample contact');
        $this->get(route('listings.edit', $property))->assertOk()->assertSee('Ground floor')->assertSee('contact@example.test');
    }

    public function test_valid_iframe_is_saved_only_as_safe_url_and_unknown_keys_are_rejected(): void
    {
        $agent = $this->agent();
        $presentation = ['tour_mode' => 'embed', 'tour_url' => '<iframe src="https://my.matterport.com/show/?m=sample" allowfullscreen></iframe>'];

        $this->actingAs($agent)->post(route('listings.store'), $this->payload(['presentation' => $presentation]))->assertSessionHasNoErrors();

        $property = Property::firstOrFail();
        $this->assertSame('https://my.matterport.com/show/?m=sample', $property->presentation['tour_url']);
        $this->assertStringNotContainsString('<iframe', json_encode($property->presentation));
        $this->post(route('listings.store'), $this->payload(['presentation' => ['tour_image' => '../../.env']]))->assertSessionHasErrors('presentation');
        $this->assertDatabaseCount('properties', 1);
    }

    public function test_script_injection_and_unapproved_embed_hosts_are_rejected(): void
    {
        $agent = $this->agent();
        $this->actingAs($agent);

        foreach (['javascript:alert(1)', 'https://my.matterport.com.evil.test/show', 'https://evil.test/', '<iframe src="https://kuula.co/share/demo"></iframe><script>alert(1)</script>'] as $url) {
            $this->post(route('listings.store'), $this->payload(['presentation' => ['tour_mode' => 'embed', 'tour_url' => $url]]))->assertSessionHasErrors('presentation.tour_url');
        }
        $this->post(route('listings.store'), $this->payload(['presentation' => ['video_url' => 'https://evil.test/movie']]))->assertSessionHasErrors('presentation.video_url');
        $this->assertDatabaseCount('properties', 0);
    }

    public function test_media_routes_preserve_draft_privacy_and_show_approved_media(): void
    {
        Storage::fake('local');
        $agent = $this->agent();
        $this->actingAs($agent)->post(route('listings.store'), $this->payload([
            'presentation' => ['tour_mode' => 'image'],
            'presentation_files' => ['tour' => $this->image()],
        ]))->assertSessionHasNoErrors();
        $property = Property::firstOrFail();
        $url = route('media.presentation', [$property, 'tour']);

        $this->get($url)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->actingAs(User::factory()->create())->get($url)->assertNotFound();
        $this->post(route('logout'))->assertRedirect();
        $this->get($url)->assertNotFound();
        $property->status = 'published';
        $property->save();
        $this->get($url)->assertOk();
        $this->get(route('media.presentation', [$property, 'floor', 999]))->assertNotFound();
    }

    public function test_floor_images_survive_edit_and_reordering_without_trusting_paths(): void
    {
        Storage::fake('local');
        $agent = $this->agent();
        $this->actingAs($agent)->post(route('listings.store'), $this->payload([
            'presentation' => ['floors_enabled' => 1, 'floors' => [['name' => 'Ground'], ['name' => 'Upper']]],
            'presentation_files' => ['floors' => [$this->image('ground.png'), $this->image('upper.png')]],
        ]))->assertSessionHasNoErrors();
        $property = Property::firstOrFail();
        $floors = $property->presentation['floors'];

        $this->put(route('listings.update', $property), $this->payload([
            'presentation' => ['floors_enabled' => 1, 'floors' => [
                ['id' => $floors[1]['id'], 'name' => 'Upper edited'],
                ['id' => $floors[0]['id'], 'name' => 'Ground edited'],
            ]],
        ]))->assertSessionHasNoErrors();

        $property->refresh();
        $this->assertSame($floors[1]['image'], $property->presentation['floors'][0]['image']);
        $this->assertSame('Upper edited', $property->presentation['floors'][0]['name']);
        Storage::disk('local')->assertExists($property->presentation['floors'][0]['image']);
        $this->put(route('listings.update', $property), $this->payload([
            'presentation' => ['floors_enabled' => 1, 'floors' => [['name' => 'Injected', 'image' => '../../.env']]],
        ]))->assertSessionHasErrors('presentation.floors.0');
    }

    public function test_floor_limits_invalid_media_and_missing_other_contact_are_validated(): void
    {
        Storage::fake('local');
        $agent = $this->agent();
        $this->actingAs($agent);
        $this->post(route('listings.store'), $this->payload(['presentation' => ['floors_enabled' => 1, 'floors' => array_fill(0, 11, ['name' => 'Floor'])]]))->assertSessionHasErrors('presentation.floors');
        $this->post(route('listings.store'), $this->payload(['presentation' => ['tour_mode' => 'image']]))->assertSessionHasErrors('presentation_files.tour');
        $this->post(route('listings.store'), $this->payload(['presentation_files' => ['tour' => UploadedFile::fake()->create('bad.svg', 1, 'image/svg+xml')]]))->assertSessionHasErrors('presentation_files.tour');
        $this->post(route('listings.store'), $this->payload(['presentation_files' => ['tour' => $this->image()->size(5121)]]))->assertSessionHasErrors('presentation_files.tour');
        $this->post(route('listings.store'), $this->payload(['presentation' => ['contact_mode' => 'other']]))->assertSessionHasErrors(['presentation.contact_name', 'presentation.contact_email']);
        $this->assertDatabaseCount('properties', 0);
    }

    public function test_validation_restores_form_values_and_escapes_user_content(): void
    {
        $agent = $this->agent();
        $this->actingAs($agent)->from(route('listings.create'))->post(route('listings.store'), $this->payload([
            'title' => '<script>bad()</script>', 'price' => 'invalid',
            'presentation' => ['floors_enabled' => 1, 'floors' => [['name' => 'Upper floor']]],
        ]))->assertRedirect(route('listings.create'))->assertSessionHasErrors('price');

        $this->get(route('listings.create'))->assertOk()->assertSee('Upper floor')->assertSee('&lt;script&gt;bad()&lt;/script&gt;', false)->assertDontSee('<script>bad()</script>', false);
        $this->assertDatabaseCount('properties', 0);
    }

    public function test_submission_stays_pending_and_cannot_reassign_owner_or_publish_itself(): void
    {
        Storage::fake('local');
        $agent = $this->agent();
        $other = $this->agent();

        $this->actingAs($agent)->post(route('listings.store'), $this->payload([
            'intent' => 'submit', 'photos' => [$this->image()], 'status' => 'published', 'user_id' => $other->id,
            'presentation' => ['unit_price' => 'per_sqft'],
        ]))->assertSessionHasNoErrors();

        $property = Property::firstOrFail();
        $this->assertSame('pending', $property->status);
        $this->assertSame($agent->id, $property->user_id);
        $this->assertStringContainsString('/ sqft', $property->priceLabel());
        $this->actingAs($other)->get(route('listings.edit', $property))->assertForbidden();
        $this->put(route('listings.update', $property), $this->payload())->assertForbidden();
        $this->assertDatabaseHas('properties', ['id' => $property->id, 'status' => 'pending', 'user_id' => $agent->id]);
    }

    public function test_save_and_preview_creates_a_private_draft_and_opens_its_preview(): void
    {
        $agent = $this->agent();

        $this->actingAs($agent)->post(route('listings.store'), $this->payload(['intent' => 'preview']))
            ->assertRedirect(route('listings.preview', Property::firstOrFail()));

        $property = Property::firstOrFail();
        $this->assertSame('draft', $property->status);
        $this->get(route('listings.preview', $property))->assertOk()->assertSee('Private preview');
    }
}
