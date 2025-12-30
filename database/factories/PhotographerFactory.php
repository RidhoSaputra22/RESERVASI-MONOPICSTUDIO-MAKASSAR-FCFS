<?php

namespace Database\Factories;

use App\Models\Photographer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Photographer>
 */
class PhotographerFactory extends Factory
{
    protected $model = Photographer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'is_available' => fake()->boolean(80),




        ];
    }
}
