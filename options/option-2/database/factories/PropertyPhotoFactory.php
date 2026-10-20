<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyPhotoFactory extends Factory
{
    public function definition(): array
    {
        return ['property_id' => Property::factory(), 'path' => 'images/home/house-1.jpg', 'position' => 0, 'is_demo' => true];
    }
}
