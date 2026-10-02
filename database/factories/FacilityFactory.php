<?php

namespace Database\Factories;

use App\Enums\FacilityCategory;
use App\Enums\MajorCode;
use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Facility>
 */
class FacilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => rtrim(fake()->unique()->sentence(2), '.'),
            'description' => fake()->sentence(12),
            'icon' => fake()->randomElement(['monitor', 'code', 'book', 'users', 'cpu']),
            'category' => FacilityCategory::Penunjang,
            'features' => [fake()->sentence(3), fake()->sentence(3)],
            'majors' => [],
            'sort_order' => 0,
        ];
    }

    /**
     * A practice room used by one major.
     */
    public function practiceRoom(MajorCode $major = MajorCode::RekayasaPerangkatLunak): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => FacilityCategory::Praktik,
            'majors' => [$major->value],
        ]);
    }
}
