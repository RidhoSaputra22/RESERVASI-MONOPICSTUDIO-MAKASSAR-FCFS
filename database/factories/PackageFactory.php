<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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

        $name = $sessionType . ' Session';
        $slug = Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 99999);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => $slug,
            'description' => fake()->optional()->paragraph(),
            'photo' => fake()->optional()->randomElement([
                null,
                'packages/' . fake()->uuid() . '.jpg',
            ]),
            'price' => fake()->randomFloat(2, 200000, 2000000),
            'duration_minutes' => fake()->randomElement([30, 60, 90, 120]),
        ];
    }
}
