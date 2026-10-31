<?php

namespace Tests\Feature;

use App\Models\ContactSubmission;
use App\Models\Conversation;
use App\Models\Property;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecaptchaTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.recaptcha.site_key' => 'test-site-key', 'services.recaptcha.secret_key' => 'test-secret']);
    }

    private function google(bool $success): void
    {
        Http::fake([self::VERIFY_URL => Http::response(['success' => $success, 'error-codes' => $success ? [] : ['invalid-input-response']])]);
    }

    private function googleDown(): void
    {
        Http::fake([self::VERIFY_URL => fn () => throw new ConnectionException('Connection timed out')]);
    }

    private function publicProperty(): Property
    {
        $property = Property::factory()->for(User::factory()->create(['role' => 'agent']), 'owner')->create();
        $property->photos()->create(['path' => 'images/home/house-1.jpg', 'is_demo' => true, 'position' => 0]);

        return $property;
    }

    public function test_checkbox_appears_on_every_protected_form(): void
    {
        foreach (['/login', '/register', '/forgot-password', '/contact', '/'] as $page) {
            $this->get($page)->assertOk()->assertSee('data-sitekey="test-site-key"', false)->assertSee('https://www.google.com/recaptcha/api.js', false);
        }
        $this->actingAs(User::factory()->create())->get('/properties/'.$this->publicProperty()->id)->assertSee('class="g-recaptcha"', false);

        $agent = User::factory()->create(['role' => 'agent']);
        $this->actingAs($agent)->get('/dashboard/properties/create')->assertOk()->assertSee('class="g-recaptcha"', false);
        $own = Property::factory()->for($agent, 'owner')->create();
        $this->get("/dashboard/properties/{$own->id}/edit")->assertOk()->assertDontSee('class="g-recaptcha"', false);
    }

    public function test_without_keys_the_check_is_off_and_forms_work_as_before(): void
    {
        config(['services.recaptcha.site_key' => null, 'services.recaptcha.secret_key' => null]);
        Http::fake();

        $this->get('/login')->assertDontSee('g-recaptcha', false)->assertDontSee('recaptcha/api.js', false);
        $this->post('/contact', ['name' => 'Visitor', 'email' => 'visitor@example.test', 'body' => 'Hello'])->assertSessionHasNoErrors();
        $this->assertSame(1, ContactSubmission::count());
        Http::assertNothingSent();
    }

    public function test_forms_reject_a_missing_or_failed_check_and_save_nothing(): void
    {
        $this->google(false);
        $usersBefore = User::count();

        $this->post('/contact', ['name' => 'Bot', 'email' => 'bot@example.test', 'body' => 'Spam'])->assertSessionHasErrors('g-recaptcha-response');
        $this->post('/contact', ['name' => 'Bot', 'email' => 'bot@example.test', 'body' => 'Spam', 'g-recaptcha-response' => 'forged'])->assertSessionHasErrors('g-recaptcha-response');
        $this->post('/newsletter', ['email' => 'bot@example.test', 'g-recaptcha-response' => 'forged'])->assertSessionHasErrors('g-recaptcha-response');
        $this->post('/register', ['name' => 'Bot', 'email' => 'bot@example.test', 'password' => 'LongEnough12', 'password_confirmation' => 'LongEnough12', 'g-recaptcha-response' => 'forged'])->assertSessionHasErrors('g-recaptcha-response');
        $this->post('/forgot-password', ['email' => 'bot@example.test', 'g-recaptcha-response' => 'forged'])->assertSessionHasErrors('g-recaptcha-response');

        $this->assertSame(0, ContactSubmission::count());
        $this->assertSame(0, Subscriber::count());
        $this->assertSame($usersBefore, User::count());

        $property = $this->publicProperty();
        $this->actingAs(User::factory()->create())->post("/properties/{$property->id}/inquiries", ['body' => 'Is it available?', 'g-recaptcha-response' => 'forged'])->assertSessionHasErrors('g-recaptcha-response');
        $this->assertSame(0, Conversation::count());

        $propertiesBefore = Property::count();
        $this->actingAs(User::factory()->create(['role' => 'agent']))->post('/dashboard/properties', ['title' => 'Bot listing', 'intent' => 'draft', 'g-recaptcha-response' => 'forged'])->assertSessionHasErrors('g-recaptcha-response');
        $this->assertSame($propertiesBefore, Property::count());
    }

    public function test_a_genuine_check_is_verified_with_google_and_accepted(): void
    {
        $this->google(true);

        $this->post('/contact', ['name' => 'Visitor', 'email' => 'visitor@example.test', 'body' => 'Hello', 'g-recaptcha-response' => 'token-123'])->assertSessionHasNoErrors();
        $this->post('/newsletter', ['email' => 'visitor@example.test', 'g-recaptcha-response' => 'token-456'])->assertSessionHasNoErrors();
        $this->post('/register', ['name' => 'New Customer', 'email' => 'new@example.test', 'password' => 'LongEnough12', 'password_confirmation' => 'LongEnough12', 'g-recaptcha-response' => 'token-789'])->assertRedirect('/dashboard');

        $this->assertSame(1, ContactSubmission::count());
        $this->assertSame(1, Subscriber::count());
        $this->assertSame('customer', User::where('email', 'new@example.test')->value('role'));
        Http::assertSent(fn (Request $request) => $request->url() === self::VERIFY_URL
            && $request['secret'] === 'test-secret' && $request['response'] === 'token-123' && $request['remoteip'] === '127.0.0.1');
    }

    public function test_login_needs_the_check_even_with_the_right_password(): void
    {
        $user = User::factory()->create();
        Http::fake([self::VERIFY_URL => Http::sequence()->push(['success' => false, 'error-codes' => ['invalid-input-response']])->push(['success' => true])]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password', 'g-recaptcha-response' => 'forged'])->assertSessionHasErrors('g-recaptcha-response');
        $this->assertGuest();

        $this->post('/login', ['email' => $user->email, 'password' => 'password', 'g-recaptcha-response' => 'token-ok'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_if_google_is_unreachable_forms_fail_closed_but_login_still_works(): void
    {
        $this->googleDown();
        $user = User::factory()->create();

        $this->post('/contact', ['name' => 'Visitor', 'email' => 'visitor@example.test', 'body' => 'Hello', 'g-recaptcha-response' => 'token'])
            ->assertSessionHasErrors(['g-recaptcha-response' => 'We could not check that you are human right now. Please try again in a moment.']);
        $this->assertSame(0, ContactSubmission::count());

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password', 'g-recaptcha-response' => 'token'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'password', 'g-recaptcha-response' => 'token'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
}
