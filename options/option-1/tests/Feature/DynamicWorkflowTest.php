<?php

namespace Tests\Feature;

use App\Models\Amenity;
use App\Models\ContactSubmission;
use App\Models\Conversation;
use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DynamicWorkflowTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_default_seeding_never_creates_demo_accounts_or_listings_and_preserves_existing_data(): void
    {
        app()->detectEnvironment(fn () => 'local');
        $property = Property::factory()->create(['title' => 'My existing property']);
        $trashed = Property::factory()->create();
        $trashed->delete();
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('properties', 2);
        $this->assertSame('My existing property', $property->fresh()->title);
        $this->assertSoftDeleted($trashed);
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseMissing('users', ['email' => 'admin@homelengo.test']);
        $this->assertDatabaseMissing('properties', ['is_demo' => true]);
    }

    private function person(string $role = 'customer'): User
    {
        $user = User::factory()->create();
        $user->role = $role;
        $user->save();

        return $user;
    }

    private function photo(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('home.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j7ioAAAAASUVORK5CYII='));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge(['title' => 'A newly submitted home', 'description' => 'A welcoming property with a sunny balcony.', 'address' => '12 Park Road', 'location' => 'Indore', 'country' => 'India', 'type' => 'Apartment', 'purpose' => 'rent', 'price' => 35000, 'rental_period' => 'month', 'area' => 1200, 'rooms' => 3, 'bedrooms' => 2, 'bathrooms' => 2, 'garages' => 1, 'availability' => 'available', 'intent' => 'submit', 'photos' => [$this->photo()]], $overrides);
    }

    private function propertyWithPhoto(User $agent, array $attributes = []): Property
    {
        $property = Property::factory()->for($agent, 'owner')->create($attributes);
        $property->photos()->create(['path' => 'images/home/house-1.jpg', 'is_demo' => true, 'position' => 0]);

        return $property;
    }

    public function test_registration_always_creates_customer_and_login_logout_work(): void
    {
        $this->post('/register', ['name' => 'New Customer', 'email' => 'new@example.test', 'password' => 'SecurePass123', 'password_confirmation' => 'SecurePass123', 'role' => 'admin'])->assertRedirect('/dashboard');
        $user = User::where('email', 'new@example.test')->firstOrFail();
        $this->assertSame('customer', $user->role);
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $user->email, 'password' => 'SecurePass123'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_promotes_agent_submits_approves_customer_finds_and_inquires_agent_replies(): void
    {
        Storage::fake('local');
        $admin = $this->person('admin');
        $agent = $this->person();
        $customer = $this->person();

        $this->actingAs($admin)->patch(route('admin.role', $agent), ['role' => 'agent'])->assertSessionHasNoErrors();
        $this->assertSame('agent', $agent->fresh()->role);
        $agent->refresh();
        $this->actingAs($agent)->post(route('listings.store'), $this->payload())->assertRedirect(route('listings.index'))->assertSessionHasNoErrors();
        $property = Property::where('user_id', $agent->id)->firstOrFail();
        $this->assertSame('pending', $property->status);
        $this->get(route('properties.show', $property))->assertNotFound();
        $this->actingAs($admin)->patch(route('listings.moderate', $property), ['decision' => 'published'])->assertSessionHasNoErrors();
        $this->assertSame('published', $property->fresh()->status);
        $this->actingAs($customer)->get('/properties?location=Indore')->assertOk()->assertSee($property->title);
        $this->post(route('favorites.store', $property))->assertSessionHasNoErrors();
        $this->post(route('inquiries.store', $property), ['body' => 'May I arrange a viewing?'])->assertRedirect();
        $conversation = Conversation::firstOrFail();
        $this->assertDatabaseHas('messages', ['conversation_id' => $conversation->id, 'user_id' => $customer->id, 'body' => 'May I arrange a viewing?']);
        $this->actingAs($agent)->get(route('conversations.show', $conversation))->assertOk()->assertSee('May I arrange a viewing?');
        $this->post(route('conversations.reply', $conversation), ['body' => 'Yes, Saturday is available.'])->assertRedirect();
        $this->actingAs($customer)->get(route('conversations.show', $conversation))->assertOk()->assertSee('Yes, Saturday is available.');
        $this->assertDatabaseHas('favorites', ['user_id' => $customer->id, 'property_id' => $property->id]);
    }

    public function test_customer_and_other_agent_cannot_manage_a_listing_or_assign_roles(): void
    {
        $agent = $this->person('agent');
        $other = $this->person('agent');
        $customer = $this->person();
        $property = $this->propertyWithPhoto($agent);
        $this->actingAs($other)->get(route('listings.edit', $property))->assertForbidden();
        $this->delete(route('listings.destroy', $property))->assertForbidden();
        $this->actingAs($customer)->post(route('listings.store'), $this->payload())->assertForbidden();
        $this->patch(route('admin.role', $other), ['role' => 'admin'])->assertForbidden();
        $this->get('/dashboard/users')->assertForbidden();
        $this->assertSame('published', $property->fresh()->status);
    }

    public function test_update_reenters_review_and_rejection_restore_are_persisted(): void
    {
        Storage::fake('local');
        $agent = $this->person('agent');
        $admin = $this->person('admin');
        $property = $this->propertyWithPhoto($agent);
        $this->actingAs($agent)->put(route('listings.update', $property), $this->payload(['photos' => [], 'title' => 'Edited home', 'status' => 'published', 'user_id' => $admin->id]))->assertSessionHasNoErrors();
        $this->assertSame('pending', $property->fresh()->status);
        $this->assertSame($agent->id, $property->fresh()->user_id);
        $this->actingAs($admin)->patch(route('listings.moderate', $property), ['decision' => 'rejected', 'rejection_reason' => 'Please add a clearer description.'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('properties', ['id' => $property->id, 'status' => 'rejected', 'rejection_reason' => 'Please add a clearer description.']);
        $this->actingAs($agent)->delete(route('listings.destroy', $property))->assertRedirect();
        $this->assertSoftDeleted($property);
        $this->post(route('listings.restore', $property->id))->assertRedirect();
        $this->assertSame('draft', $property->fresh()->status);
        $this->assertFalse($property->fresh()->trashed());
    }

    public function test_submission_requires_photo_and_validates_upload_type_size_and_count(): void
    {
        Storage::fake('local');
        $agent = $this->person('agent');
        $this->actingAs($agent)->post(route('listings.store'), $this->payload(['photos' => []]))->assertSessionHasErrors('photos');
        $this->post(route('listings.store'), $this->payload(['photos' => [UploadedFile::fake()->create('bad.php', 2, 'text/plain')]]))->assertSessionHasErrors('photos.0');
        $large = $this->photo()->size(5121);
        $this->post(route('listings.store'), $this->payload(['photos' => [$large]]))->assertSessionHasErrors('photos.0');
        $photos = [];
        for ($i = 0; $i < 21; $i++) {
            $photos[] = $this->photo();
        }
        $this->post(route('listings.store'), $this->payload(['photos' => $photos]))->assertSessionHasErrors('photos');
        $this->assertDatabaseCount('properties', 0);
    }

    public function test_photo_order_cover_removal_and_cross_property_ids_are_checked(): void
    {
        $agent = $this->person('agent');
        $property = $this->propertyWithPhoto($agent);
        $second = $property->photos()->create(['path' => 'images/home/house-2.jpg', 'is_demo' => true, 'position' => 1]);
        $first = $property->photos()->first();
        $other = $this->propertyWithPhoto($agent);
        $this->actingAs($agent)->put(route('listings.update', $property), $this->payload(['photos' => [], 'cover_photo' => $other->photos()->first()->id]))->assertSessionHasErrors('photos');
        $this->put(route('listings.update', $property), $this->payload(['photos' => [], 'cover_photo' => $second->id, 'photo_order' => [$second->id, $first->id]]))->assertSessionHasNoErrors();
        $this->assertSame($second->id, $property->fresh()->photos->first()->id);
        $this->put(route('listings.update', $property), $this->payload(['photos' => [], 'remove_photos' => [$first->id]]))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('property_photos', ['id' => $first->id]);
    }

    public function test_private_media_and_dashboard_preview_require_owner_or_admin(): void
    {
        $agent = $this->person('agent');
        $other = $this->person();
        $property = $this->propertyWithPhoto($agent, ['status' => 'pending']);
        $photo = $property->photos()->first();
        $this->get(route('media.photo', $photo))->assertNotFound();
        $this->actingAs($other)->get(route('listings.preview', $property))->assertForbidden();
        $this->get(route('media.photo', $photo))->assertNotFound();
        $this->actingAs($agent)->get(route('listings.preview', $property))->assertOk()->assertSee('Private preview');
        $this->get(route('media.photo', $photo))->assertOk();
    }

    public function test_search_filters_and_sorting_exclude_unpublished_sold_and_deleted(): void
    {
        $amenity = Amenity::create(['name' => 'Pool']);
        $match = Property::factory()->create(['title' => 'Pool home', 'price' => 40000, 'area' => 1500, 'bedrooms' => 3]);
        $match->amenities()->attach($amenity);
        Property::factory()->create(['title' => 'Hidden home', 'status' => 'pending']);
        Property::factory()->create(['title' => 'Sold home', 'availability' => 'sold']);
        Property::factory()->create(['title' => 'Deleted home'])->delete();
        $this->get('/properties?location=Indore&type=Apartment&purpose=rent&min_price=35000&max_price=45000&bedrooms=3&bathrooms=2&min_area=1300&max_area=1800&amenities[]='.$amenity->id.'&sort=price_asc')->assertOk()->assertSee('Pool home')->assertDontSee('Hidden home')->assertDontSee('Sold home')->assertDontSee('Deleted home');
        $this->get('/properties?sort=price;DROP TABLE users')->assertSessionHasErrors('sort');
    }

    public function test_favorites_are_idempotent_and_only_current_user_can_remove_theirs(): void
    {
        $a = $this->person();
        $b = $this->person();
        $property = Property::factory()->create();
        $this->actingAs($a)->post(route('favorites.store', $property));
        $this->post(route('favorites.store', $property));
        $this->actingAs($b)->post(route('favorites.store', $property));
        $this->delete(route('favorites.destroy', $property))->assertRedirect();
        $this->assertDatabaseCount('favorites', 1);
        $this->assertDatabaseHas('favorites', ['user_id' => $a->id, 'property_id' => $property->id]);
        $this->actingAs($a)->get('/dashboard/favorites')->assertOk()->assertSee($property->title);
    }

    public function test_conversation_access_and_unread_counts_are_private(): void
    {
        $agent = $this->person('agent');
        $customer = $this->person();
        $stranger = $this->person();
        $property = $this->propertyWithPhoto($agent);
        $this->actingAs($customer)->post(route('inquiries.store', $property), ['body' => 'Private question']);
        $conversation = Conversation::firstOrFail();
        $this->actingAs($stranger)->get(route('conversations.show', $conversation))->assertForbidden();
        $this->post(route('conversations.reply', $conversation), ['body' => 'Intrusion'])->assertForbidden();
        $this->get('/dashboard/messages')->assertDontSee('Private question');
        $this->actingAs($agent)->get('/dashboard/unread')->assertJson(['unread' => 1]);
        $this->get(route('conversations.show', $conversation))->assertOk();
        $this->get('/dashboard/unread')->assertJson(['unread' => 0]);
        $this->assertDatabaseCount('messages', 1);
    }

    public function test_reviews_require_moderation_remain_unique_and_owner_cannot_review(): void
    {
        $agent = $this->person('agent');
        $customer = $this->person();
        $admin = $this->person('admin');
        $property = $this->propertyWithPhoto($agent);
        $this->actingAs($customer)->post(route('reviews.store', $property), ['rating' => 5, 'body' => 'A lovely sample review'])->assertSessionHasNoErrors();
        $review = Review::firstOrFail();
        $this->assertSame('pending', $review->status);
        $this->get(route('properties.show', $property))->assertViewHas('property', fn ($property) => $property->reviews->isEmpty());
        $this->actingAs($admin)->patch(route('reviews.moderate', $review), ['status' => 'approved'])->assertSessionHasNoErrors();
        $this->get(route('properties.show', $property))->assertSee('A lovely sample review');
        $this->actingAs($customer)->post(route('reviews.store', $property), ['rating' => 4, 'body' => 'Updated review']);
        $this->assertDatabaseCount('reviews', 1);
        $this->assertSame('pending', $review->fresh()->status);
        $this->actingAs($agent)->post(route('reviews.store', $property), ['rating' => 5, 'body' => 'My home'])->assertForbidden();
        $this->actingAs($customer)->delete(route('reviews.destroy', $review))->assertRedirect();
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_profile_upload_password_and_role_protection(): void
    {
        Storage::fake('local');
        $customer = $this->person();
        $this->actingAs($customer)->put('/dashboard/profile', ['name' => 'Updated Name', 'email' => $customer->email, 'phone' => '9876543210', 'role' => 'admin', 'avatar' => $this->photo()])->assertSessionHasNoErrors();
        $this->assertSame('customer', $customer->fresh()->role);
        $this->assertSame('Updated Name', $customer->fresh()->name);
        Storage::disk('local')->assertExists($customer->fresh()->avatar_path);
        $this->put('/dashboard/password', ['current_password' => 'incorrect', 'password' => 'ChangedPass123', 'password_confirmation' => 'ChangedPass123'])->assertSessionHasErrors('current_password');
        $this->put('/dashboard/password', ['current_password' => 'password', 'password' => 'ChangedPass123', 'password_confirmation' => 'ChangedPass123'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('ChangedPass123', $customer->fresh()->password));
    }

    public function test_password_recovery_uses_broker_and_reset_token(): void
    {
        Notification::fake();
        $user = $this->person();
        $token = null;
        $this->post('/forgot-password', ['email' => $user->email])->assertRedirect();
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token) {
            $token = $notification->token;

            return true;
        });
        $this->get('/reset-password/'.$token.'?email='.urlencode($user->email))->assertOk();
        $this->post('/reset-password', ['email' => $user->email, 'token' => $token, 'password' => 'Recovered123', 'password_confirmation' => 'Recovered123'])->assertRedirect('/login');
        $this->assertTrue(Hash::check('Recovered123', $user->fresh()->password));
    }

    public function test_contact_and_newsletter_are_stored_and_admin_can_handle(): void
    {
        $this->post('/contact', ['name' => 'Visitor', 'email' => 'visitor@example.test', 'body' => 'Please contact me.'])->assertSessionHasNoErrors();
        $this->post('/newsletter', ['email' => 'visitor@example.test'])->assertRedirect();
        $this->post('/newsletter', ['email' => 'visitor@example.test'])->assertRedirect();
        $this->assertDatabaseCount('subscribers', 1);
        $contact = ContactSubmission::firstOrFail();
        $this->actingAs($this->person('admin'))->get('/dashboard/inbox')->assertOk()->assertSee('Please contact me.');
        $this->patch(route('admin.handled', $contact))->assertRedirect();
        $this->assertDatabaseHas('contact_submissions', ['id' => $contact->id, 'handled' => true]);
    }

    public function test_views_are_counted_once_per_session_and_dashboard_aggregates_are_scoped(): void
    {
        $agent = $this->person('agent');
        $other = $this->person('agent');
        $property = $this->propertyWithPhoto($agent, ['status' => 'published']);
        $this->get(route('properties.show', $property))->assertOk();
        $this->get(route('properties.show', $property))->assertOk();
        $this->assertDatabaseHas('property_views', ['property_id' => $property->id, 'views' => 1]);
        $this->actingAs($agent)->get('/dashboard')->assertOk()->assertViewHas('counts', fn ($counts) => $counts['Listings'] === 1 && $counts['Property views'] === 1);
        $this->actingAs($other)->get('/dashboard')->assertOk()->assertViewHas('counts', fn ($counts) => $counts['Listings'] === 0 && $counts['Property views'] === 0);
    }

    public function test_user_supplied_text_is_escaped_in_property_and_messages(): void
    {
        $agent = $this->person('agent');
        $customer = $this->person();
        $property = $this->propertyWithPhoto($agent, ['title' => '<script>alert(1)</script>']);
        $this->get(route('properties.show', $property))->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
        $this->actingAs($customer)->post(route('inquiries.store', $property), ['body' => '<script>steal()</script>']);
        $this->get(route('conversations.show', Conversation::firstOrFail()))->assertOk()->assertDontSee('<script>steal()</script>', false);
    }

    public function test_guest_and_low_privilege_users_cannot_use_dashboard_write_routes(): void
    {
        $this->get('/dashboard/profile')->assertRedirect('/login');
        $this->post('/dashboard/properties', $this->payload())->assertRedirect('/login');
        $user = $this->person();
        $this->actingAs($user)->get('/dashboard/properties/create')->assertForbidden();
        $this->patch(route('admin.role', $user), ['role' => 'agent'])->assertForbidden();
        $this->assertSame('customer', $user->fresh()->role);
    }
}
