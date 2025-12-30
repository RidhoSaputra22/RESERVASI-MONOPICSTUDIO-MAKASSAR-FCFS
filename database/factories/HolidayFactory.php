<?php

namespace Database\Factories;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Holiday>
 */
class HolidayFactory extends Factory
{
    protected $model = Holiday::class;

    public function definition(): array
    {
        return [
            'date' => fake()->unique()->dateTimeBetween('+1 week', '+1 year')->format('Y-m-d'),
            'description' => fake()->optional()->sentence(4),
        ];
    }
}
