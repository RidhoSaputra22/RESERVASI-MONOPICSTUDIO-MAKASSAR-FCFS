<?php

namespace Database\Factories;

use App\Models\Studio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Studio>
 */
class StudioFactory extends Factory
{
    protected $model = Studio::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->bothify('Studio ?'),
            'location' => fake()->optional()->streetAddress(),
            'capacity' => fake()->numberBetween(1, 4),
        ];
    }
}
