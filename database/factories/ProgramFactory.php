<?php

namespace Database\Factories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(3), '.');

        return [
            'slug' => Str::slug($title),
            'title' => $title,
            'description' => fake()->sentence(15),
            'icon' => fake()->randomElement(['briefcase', 'factory', 'award', 'globe', 'bulb', 'book']),
            'image' => null,
            'audience' => 'Seluruh siswa',
            'schedule' => 'Setiap pekan',
            'body' => [
                fake()->paragraph(),
                ['list' => [fake()->sentence(), fake()->sentence()]],
            ],
            'sort_order' => 0,
        ];
    }
}
