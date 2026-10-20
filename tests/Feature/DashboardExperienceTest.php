<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class DashboardExperienceTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function account(string $role): User
    {
        $user = User::factory()->create();
        $user->role = $role;
        $user->save();

        return $user;
    }

    #[TestWith(['admin'])]
    #[TestWith(['agent'])]
    public function test_management_dashboard_links_statistics_and_status_counts_to_working_filters(string $role): void
    {
        $owner = $this->account($role);
        Property::factory()->for($owner, 'owner')->create(['status' => 'published']);
        Property::factory()->for($owner, 'owner')->create(['status' => 'pending']);
        Property::factory()->for($owner, 'owner')->create(['status' => 'draft']);
        Property::factory()->for($owner, 'owner')->create(['status' => 'rejected']);
        Property::factory()->for($owner, 'owner')->create(['status' => 'published'])->delete();

        $this->actingAs($owner)->get('/dashboard')->assertOk()
            ->assertSee('Dashboard statistics')
            ->assertSee('Quick actions')
            ->assertSee('Listing health')
            ->assertSee('href="'.route('listings.index', ['status' => 'pending']).'"', false)
            ->assertSee('href="#property-views-chart"', false)
            ->assertViewHas('statusBreakdown', fn ($statuses) => $statuses->all() === ['draft' => 1, 'pending' => 1, 'published' => 1, 'rejected' => 1])
            ->assertViewHas('counts', fn ($counts) => $counts['Listings'] === 4 && $counts['Pending approval'] === 1);
    }

    public function test_agent_health_excludes_other_owners_even_when_they_have_pending_properties(): void
    {
        $owner = $this->account('agent');
        Property::factory()->for($owner, 'owner')->create(['status' => 'draft']);
        Property::factory()->create(['status' => 'pending', 'title' => 'Other owner private home']);

        $this->actingAs($owner)->get('/dashboard')->assertOk()
            ->assertViewHas('statusBreakdown', fn ($statuses) => $statuses->all() === ['draft' => 1])
            ->assertDontSee('Other owner private home')
            ->assertDontSee('Review pending')
            ->assertSee('Track approvals');
    }

    public function test_customer_gets_discovery_actions_and_a_private_conversation_total_without_agent_controls(): void
    {
        $customer = $this->account('customer');
        $property = Property::factory()->create();
        Conversation::create(['property_id' => $property->id, 'customer_id' => $customer->id, 'agent_id' => $property->user_id]);
        $other = Property::factory()->create();
        Conversation::create(['property_id' => $other->id, 'customer_id' => User::factory()->create()->id, 'agent_id' => $other->user_id]);

        $this->actingAs($customer)->get('/dashboard')->assertOk()
            ->assertViewHas('conversationTotal', 1)
            ->assertSee('Find a property')
            ->assertSee('Make room for your next move.')
            ->assertDontSee('Listing health')
            ->assertDontSee('href="'.route('listings.create').'"', false)
            ->assertDontSee('href="'.route('admin.users').'"', false);
    }

    public function test_dashboard_conversations_show_incoming_unread_counts_without_marking_them_read(): void
    {
        $agent = $this->account('agent');
        $customer = $this->account('customer');
        $property = Property::factory()->for($agent, 'owner')->create();
        $conversation = Conversation::create(['property_id' => $property->id, 'customer_id' => $customer->id, 'agent_id' => $agent->id]);
        $message = $conversation->messages()->create(['user_id' => $customer->id, 'body' => 'Can we arrange a viewing?']);
        $conversation->messages()->create(['user_id' => $agent->id, 'body' => 'Please choose a day.']);

        $this->actingAs($agent)->get('/dashboard')->assertOk()
            ->assertSee('1 unread')
            ->assertSee('Please choose a day.')
            ->assertViewHas('conversations', fn ($conversations) => $conversations->first()->unread_count === 1);

        $this->assertNull($message->fresh()->read_at);
    }

    public function test_dashboard_styling_and_confirmation_controls_do_not_leak_into_homez_homepage(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('elementor-element-d30cad4', false)
            ->assertDontSee('/css/dashboard.css', false)
            ->assertDontSee('id="workspace-confirm"', false);
    }

    public function test_listing_actions_have_recoverable_trash_confirmation_and_accessible_mobile_navigation(): void
    {
        $agent = $this->account('agent');
        Property::factory()->for($agent, 'owner')->create();

        $this->actingAs($agent)->get('/dashboard')->assertOk()
            ->assertSee('data-confirm-trash', false)
            ->assertSee('aria-label="Edit" title="Edit"', false)
            ->assertSee('aria-label="Preview" title="Preview"', false)
            ->assertSee('aria-label="Trash" title="Trash"', false)
            ->assertSee('id="workspace-confirm"', false)
            ->assertSee('Toggle dashboard menu')
            ->assertSee('Keep listing')
            ->assertSee('/js/dashboard.js', false);
    }

    public function test_approval_updates_home_two_results_and_dashboard_health_from_the_same_property(): void
    {
        $admin = $this->account('admin');
        $property = Property::factory()->create(['title' => 'Connected approval audit home', 'status' => 'pending', 'purpose' => 'sale']);
        $property->photos()->create(['path' => 'property-photos/audit.jpg', 'position' => 0]);

        $this->get('/')->assertOk()->assertDontSee('Connected approval audit home');
        $this->actingAs($admin)->patch(route('listings.moderate', $property), ['decision' => 'published'])->assertSessionHasNoErrors();
        $this->get('/')->assertOk()->assertSee('Connected approval audit home')->assertViewHas('saleCount', 1);
        $this->get('/dashboard')->assertOk()->assertViewHas('statusBreakdown', fn ($statuses) => $statuses->all() === ['published' => 1]);

        $this->assertDatabaseHas('properties', ['id' => $property->id, 'status' => 'published']);
    }
}
