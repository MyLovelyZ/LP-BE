<?php

namespace Database\Factories;

use App\Enums\NewsCategory;
use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<News>
 */
class NewsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(6), '.');

        return [
            'slug' => Str::slug($title),
            'title' => $title,
            'category' => fake()->randomElement(NewsCategory::cases()),
            'excerpt' => fake()->paragraph(),
            'body' => [
                fake()->paragraph(),
                ['heading' => rtrim(fake()->sentence(3), '.')],
                fake()->paragraph(),
            ],
            'image' => null,
            'author' => null,
            'is_published' => true,
            'published_at' => fake()->dateTimeBetween('-3 months', '-1 hour'),
        ];
    }

    /**
     * Saved but hidden from visitors.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }

    /**
     * Published with a future date, so visitors only see it once that date arrives.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => now()->addWeek(),
        ]);
    }
}
