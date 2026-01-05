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

        $photo = [
            'packages/gallery-1.jpg',
            'packages/gallery-2.jpg',
            'packages/gallery-4.jpg',
            'packages/gallery-5.jpg',
        ];

        $name = $sessionType . ' Session';
        $slug = Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 99999);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => $slug,
            'description' => fake()->paragraph(),
            'fasilitas' => collect([
                'Free cetak foto',
                'Makeup basic',
                'Akses semua properti',
            ])->shuffle()->take(fake()->numberBetween(1, 3))->implode("\n"),
            // 'photo' => fake()->randomElement(array_merge([null], $photo)),
            'photo' => fake()->randomElement($photo),
            'price' => 200000,
            'duration_minutes' => fake()->randomElement([30, 60, 90, 120]),
        ];
    }
}
