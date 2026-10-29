<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_password_change_signs_out_other_sessions_but_keeps_the_current_one(): void
    {
        $user = User::factory()->create(['remember_token' => 'old-remember-token']);

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $otherDevice = session()->all();

        $this->put('/dashboard/password', ['current_password' => 'password', 'password' => 'ChangedPass123', 'password_confirmation' => 'ChangedPass123'])->assertSessionHasNoErrors();
        $this->assertNotSame('old-remember-token', $user->fresh()->remember_token);
        $this->get('/dashboard')->assertOk();

        $this->flushSession();
        $this->withSession($otherDevice)->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_password_reset_signs_out_existing_sessions(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/dashboard')->assertOk();
        $session = session()->all();

        $user->forceFill(['password' => Hash::make('ResetElsewhere123')])->save();

        $this->flushSession();
        $this->withSession($session)->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_new_passwords_need_at_least_twelve_characters(): void
    {
        $this->post('/register', ['name' => 'Short', 'email' => 'short@example.test', 'password' => 'Short123456', 'password_confirmation' => 'Short123456'])->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'short@example.test']);

        $this->post('/register', ['name' => 'Long', 'email' => 'long@example.test', 'password' => 'LongEnough12', 'password_confirmation' => 'LongEnough12'])->assertRedirect('/dashboard');
    }

    public function test_changing_email_requires_the_current_password(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.test']);
        $this->actingAs($user);

        $this->put('/dashboard/profile', ['name' => $user->name, 'email' => 'attacker@example.test'])->assertSessionHasErrors('current_password_for_email');
        $this->put('/dashboard/profile', ['name' => $user->name, 'email' => 'attacker@example.test', 'current_password_for_email' => 'wrong-password'])->assertSessionHasErrors('current_password_for_email');
        $this->assertSame('owner@example.test', $user->fresh()->email);

        $this->put('/dashboard/profile', ['name' => 'Renamed', 'email' => 'owner@example.test'])->assertSessionHasNoErrors();
        $this->put('/dashboard/profile', ['name' => 'Renamed', 'email' => 'new@example.test', 'current_password_for_email' => 'password'])->assertSessionHasNoErrors();
        $this->assertSame('new@example.test', $user->fresh()->email);
    }

    public function test_login_attempts_are_limited_per_ip_across_accounts(): void
    {
        for ($attempt = 1; $attempt <= 30; $attempt++) {
            $this->post('/login', ['email' => "user{$attempt}@example.test", 'password' => 'not-the-password'])->assertSessionHasErrors('email');
        }

        $this->post('/login', ['email' => 'user31@example.test', 'password' => 'not-the-password'])->assertStatus(429);
    }

    public function test_daily_dashboard_chart_is_limited_to_one_year(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/dashboard?period=day&chart_from='.today()->subDays(400)->toDateString())->assertSessionHasErrors('chart_from');
        $this->get('/dashboard?period=day&chart_from='.today()->subDays(300)->toDateString())->assertOk();
        $this->get('/dashboard?period=month&chart_from='.today()->subYears(4)->toDateString())->assertOk();
    }
}
