<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationFactory extends Factory
{
    public function definition(): array
    {
        return ['property_id' => Property::factory(), 'customer_id' => User::factory(), 'agent_id' => User::factory()];
    }
}
