<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package>
 */
class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        $sessionType = fake()->randomElement([
            'Couple',
            'Family',
            'Prewedding',
            'Graduation',
            'Maternity',
            'Solo',
        ]);

        return [








            'name' => $sessionType . ' Session',
            'description' => fake()->optional()->paragraph(),
            'price' => fake()->randomFloat(2, 200000, 2000000),
            'duration_minutes' => fake()->randomElement([30, 60, 90, 120]),
        ];
    }
}
