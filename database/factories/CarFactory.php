<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Car>
 */
class CarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->unique()->words(3, true),
            'brand' => fake()->randomElement(['Toyota', 'BMW', 'Mercedes', 'Ford']),
            'model' => fake()->bothify('Model ##'),
            'year' => fake()->numberBetween(2018, (int) date('Y') + 1),
            'color' => fake()->safeColorName(),
            'description' => fake()->paragraph(),
            'specifications' => ['transmission' => 'Automatic'],
            'currency' => 'AED',
            'is_published' => false,
            'is_featured' => false,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_published' => true,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_published' => true,
            'is_featured' => true,
        ]);
    }
}
