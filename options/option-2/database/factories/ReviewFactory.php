<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return ['property_id' => Property::factory(), 'user_id' => User::factory(), 'rating' => fake()->numberBetween(1, 5), 'body' => fake()->paragraph(), 'status' => 'pending'];
    }
}
