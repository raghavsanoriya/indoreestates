<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class DashboardDesignTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function account(string $role = 'agent'): User
    {
        $user = User::factory()->create();
        $user->role = $role;
        $user->save();

        return $user;
    }

    public function test_overview_uses_original_components_with_scoped_database_listings(): void
    {
        $owner = $this->account();
        Property::factory()->for($owner, 'owner')->create(['title' => 'My real database listing']);
        Property::factory()->create(['title' => 'Another agent private listing']);

        $this->actingAs($owner)->get(route('dashboard'))->assertOk()
            ->assertSee('/themes/homez/css/template5152.css', false)
            ->assertSee('class="table-responsive"', false)
            ->assertSee('id="property-views-chart"', false)
            ->assertSee('aria-label="Dashboard navigation"', false)
            ->assertSee('My real database listing')
            ->assertDontSee('Another agent private listing')
            ->assertDontSee('YOUR PROPERTY WORKSPACE')
            ->assertDontSee('/50 remaining')
            ->assertViewHas('counts', fn ($counts) => $counts['Listings'] === 1);
    }

    #[TestWith(['customer'])]
    #[TestWith(['agent'])]
    #[TestWith(['admin'])]
    public function test_all_dashboard_pages_share_the_original_frame_and_role_navigation(string $role): void
    {
        $user = $this->account($role);
        $routes = ['dashboard', 'profile.edit', 'favorites.index', 'conversations.index', 'reviews.index'];
        if ($user->canList()) {
            $routes[] = 'listings.index';
            $routes[] = 'listings.create';
        }
        if ($user->isAdmin()) {
            $routes[] = 'admin.users';
            $routes[] = 'admin.inbox';
        }
        $this->actingAs($user);

        foreach ($routes as $route) {
            $response = $this->get(route($route))->assertOk()
                ->assertSee('/themes/homez/css/template5152.css', false)
                ->assertSee('/js/app.js', false)
                ->assertSee('class="breadcrumb-bar o3-breadcrumb"', false)
                ->assertSee('id="workspace-sidebar"', false)
                ->assertSee('aria-label="Toggle dashboard sidebar"', false)
                ->assertSee('class="workspace-footer"', false)
                ->assertSee('action="'.route('logout').'"', false)
                ->assertDontSee('class="dashboard-shell"', false)
                ->assertDontSee('reference-sidebar', false);
            if ($role === 'customer') {
                $response->assertDontSee('href="'.route('listings.create').'"', false);
            }
        }
    }

    public function test_account_menu_preserves_full_name_and_dashboard_controls_do_not_leak_to_public_pages(): void
    {
        $user = User::factory()->create(['name' => 'Indore Estate Workspace Administrator']);

        $this->actingAs($user)->get(route('dashboard'))->assertOk()
            ->assertSee('<span class="o3-account-name">Indore Estate Workspace Administrator</span>', false)
            ->assertSee('aria-controls="workspace-sidebar"', false)
            ->assertSee('aria-label="Open messages"', false);

        $this->get(route('home'))->assertOk()
            ->assertDontSee('data-workspace-toggle', false)
            ->assertDontSee('class="workspace-footer"', false)
            ->assertSee('id="apus-footer"', false);
    }

    #[TestWith(['customer'])]
    #[TestWith(['agent'])]
    #[TestWith(['admin'])]
    public function test_profile_dropdown_uses_avatar_and_connected_role_appropriate_actions(string $role): void
    {
        $user = $this->account($role);

        $response = $this->actingAs($user)->get(route('profile.edit'))->assertOk()
            ->assertSee('aria-label="Account navigation"', false)
            ->assertSee('aria-controls="account-navigation"', false)
            ->assertSee('class="o3-account-avatar"', false)
            ->assertSee('data-account-dropdown', false)
            ->assertSee('id="change-password"', false)
            ->assertSee('href="'.route('profile.edit').'#change-password"', false)
            ->assertSee('Logout')
            ->assertDontSee('Saved Search')
            ->assertDontSee('My Package');

        if ($role === 'customer') {
            $response->assertDontSee('Submit Property')->assertDontSee('User Access');
        } else {
            $response->assertSee('Submit Property');
        }
    }

    #[TestWith(['day', 14])]
    #[TestWith(['week', 12])]
    #[TestWith(['month', 12])]
    #[TestWith(['year', 5])]
    public function test_chart_periods_use_scoped_stored_views(string $period, int $buckets): void
    {
        $this->freezeTime();
        $owner = $this->account();
        $property = Property::factory()->for($owner, 'owner')->create();
        $otherProperty = Property::factory()->create();
        $property->views()->create(['day' => today()->toDateString(), 'views' => 7]);
        $otherProperty->views()->create(['day' => today()->toDateString(), 'views' => 99]);

        $this->actingAs($owner)->get(route('dashboard', ['period' => $period]))->assertOk()
            ->assertViewHas('days', fn ($days) => $days->count() === $buckets && $days->sum('total') === 7)
            ->assertViewHas('counts', fn ($counts) => $counts['Property views'] === 7)
            ->assertSee('7 recorded views in the selected period');
    }

    public function test_overview_filters_are_working_without_changing_total_counters(): void
    {
        $this->freezeTime();
        $owner = $this->account();
        Property::factory()->for($owner, 'owner')->create(['title' => 'New matching house', 'status' => 'pending']);
        Property::factory()->for($owner, 'owner')->create(['title' => 'Old matching house', 'status' => 'pending', 'created_at' => today()->subDays(10)]);
        Property::factory()->for($owner, 'owner')->create(['title' => 'Other house', 'status' => 'published']);

        $this->actingAs($owner)->get(route('dashboard', ['keyword' => 'matching', 'status' => 'pending', 'from' => today()->toDateString(), 'to' => today()->toDateString()]))->assertOk()
            ->assertSee('New matching house')
            ->assertDontSee('Old matching house')
            ->assertDontSee('Other house')
            ->assertViewHas('properties', fn ($properties) => $properties->total() === 1)
            ->assertViewHas('counts', fn ($counts) => $counts['Listings'] === 3);
    }

    public function test_invalid_chart_periods_and_reversed_dates_are_rejected(): void
    {
        $this->actingAs($this->account());

        $this->get(route('dashboard', ['period' => 'anything']))->assertSessionHasErrors('period');
        $this->get(route('dashboard', ['chart_from' => today()->toDateString(), 'chart_to' => today()->subDay()->toDateString()]))->assertSessionHasErrors('chart_to');
        $this->get(route('dashboard', ['from' => today()->toDateString(), 'to' => today()->subDay()->toDateString()]))->assertSessionHasErrors('to');
    }

    public function test_message_panels_show_only_the_latest_authorized_conversation_messages(): void
    {
        $owner = $this->account();
        $customer = $this->account('customer');
        $property = Property::factory()->for($owner, 'owner')->create();
        $conversation = Conversation::create(['property_id' => $property->id, 'customer_id' => $customer->id, 'agent_id' => $owner->id]);
        $conversation->messages()->create(['user_id' => $customer->id, 'body' => 'Earlier authorized message', 'created_at' => now()->subMinute()]);
        $conversation->messages()->create(['user_id' => $customer->id, 'body' => 'Latest authorized message']);
        $otherOwner = $this->account();
        $otherProperty = Property::factory()->for($otherOwner, 'owner')->create();
        $private = Conversation::create(['property_id' => $otherProperty->id, 'customer_id' => $customer->id, 'agent_id' => $otherOwner->id]);
        $private->messages()->create(['user_id' => $customer->id, 'body' => 'Another agent confidential message']);

        $this->actingAs($owner)->get(route('dashboard'))->assertOk()->assertSee('Latest authorized message')->assertDontSee('Earlier authorized message')->assertDontSee('Another agent confidential message');
        $this->get(route('conversations.index'))->assertOk()->assertSee('Latest authorized message')->assertDontSee('Another agent confidential message');
    }
}
