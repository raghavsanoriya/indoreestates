<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class HomezIntegrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_homez_type_and_account_links_redirect_to_the_dynamic_laravel_routes(): void
    {
        $this->get('/property-type/apartments')->assertRedirect(route('properties.index', ['type' => 'Apartment']));
        $this->get('/property-type/office')->assertRedirect(route('properties.index', ['type' => 'Office']));
        $this->get('/property-type/unknown')->assertNotFound();
        $this->get('/submit-property')->assertRedirect('/dashboard/properties/create');
        $this->get('/my-favorites')->assertRedirect('/dashboard/favorites');
        $this->get('/source/demoapus1.com/homez/home-4/index.html')->assertNotFound();
        $this->get('/wp-admin')->assertNotFound();
    }

    public function test_public_gallery_uses_all_ordered_database_photos_with_keyboard_accessible_thumbnail_buttons(): void
    {
        Storage::fake('local');
        $property = Property::factory()->create();
        $image = UploadedFile::fake()->createWithContent('home.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j7ioAAAAASUVORK5CYII='));
        $second = $property->photos()->create(['path' => $image->store('property-photos', 'local'), 'position' => 1]);
        $first = $property->photos()->create(['path' => $image->store('property-photos', 'local'), 'position' => 0]);

        $this->get(route('properties.show', $property))->assertOk()
            ->assertSeeInOrder(['alt="'.$property->title.' photo 1"', 'alt="'.$property->title.' photo 2"'], false)
            ->assertSee('aria-label="View photo 1"', false)
            ->assertSee('aria-label="View photo 2"', false)
            ->assertSee('data-homez-photo', false)
            ->assertSee('/js/homez.js', false);
        $this->get(route('media.photo', $first))->assertOk();
        $this->get(route('media.photo', $second))->assertOk();

        Storage::disk('local')->assertExists([$first->path, $second->path]);
    }

    public function test_homez_favourite_is_still_saved_after_logout_and_sign_in(): void
    {
        $customer = User::factory()->create();
        $property = Property::factory()->create(['purpose' => 'sale']);

        $this->actingAs($customer)->post(route('favorites.store', $property))->assertSessionHasNoErrors();
        $this->post('/logout')->assertRedirect('/');
        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])->assertRedirect('/dashboard');
        $this->get('/')->assertOk()->assertSee('aria-label="Remove favourite"', false);
        $this->get('/dashboard/favorites')->assertOk()->assertSee($property->title);

        $this->assertDatabaseHas('favorites', ['property_id' => $property->id, 'user_id' => $customer->id]);
    }

    public function test_contact_validation_does_not_store_an_invalid_submission(): void
    {
        $this->post('/contact', ['name' => 'Visitor', 'email' => 'not-an-email', 'body' => 'Please call me.'])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('contact_submissions', 0);
    }

    public function test_newsletter_deduplicates_email_case_without_creating_extra_subscribers(): void
    {
        $this->post('/newsletter', ['email' => 'Reader@example.test'])->assertRedirect();
        $this->post('/newsletter', ['email' => 'reader@example.test'])->assertRedirect();

        $this->assertDatabaseCount('subscribers', 1);
        $this->assertDatabaseHas('subscribers', ['email' => 'reader@example.test']);
    }

    #[TestWith(['customer'])]
    #[TestWith(['agent'])]
    public function test_contact_and_newsletter_inbox_is_not_exposed_to_non_administrators(string $role): void
    {
        $user = User::factory()->create();
        $user->role = $role;
        $user->save();

        $this->actingAs($user)->get('/dashboard/inbox')->assertForbidden();
    }
}
