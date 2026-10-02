<?php

namespace Database\Factories;

use App\Models\Facility;
use App\Models\FacilityImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FacilityImage>
 */
class FacilityImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'facility_id' => Facility::factory(),
            'path' => 'facilities/'.fake()->uuid().'.jpg',
            'sort_order' => 0,
        ];
    }
}
