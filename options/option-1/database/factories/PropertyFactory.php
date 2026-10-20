<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'title' => fake()->streetName().' residence', 'description' => fake()->paragraph(), 'address' => fake()->streetAddress(), 'location' => 'Indore', 'country' => 'India', 'type' => 'Apartment', 'purpose' => 'rent', 'price' => 35000, 'area' => 1200, 'rooms' => 3, 'bedrooms' => 2, 'bathrooms' => 2, 'garages' => 1, 'availability' => 'available', 'status' => 'published'];
    }
}
