<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AddPropertyAmenitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Smoke alarm', 'Self check-in with lockbox', 'Carbon monoxide alarm', 'Security cameras', 'Hangers', 'Extra pillows & blankets', 'Bed linens', 'TV with standard cable', 'Refrigerator', 'Dishwasher', 'Microwave', 'Coffee maker'] as $name) {
            Amenity::firstOrCreate(['name' => $name]);
        }
    }
}
