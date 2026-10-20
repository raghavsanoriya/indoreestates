<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyViewFactory extends Factory
{
    public function definition(): array
    {
        return ['property_id' => Property::factory(), 'day' => today()->toDateString(), 'views' => fake()->numberBetween(1, 20)];
    }
}
