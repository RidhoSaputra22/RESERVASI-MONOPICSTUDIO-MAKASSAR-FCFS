<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->words(2, true));
        $slug = Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 99999);

        return [
            'name' => $name,
            'slug' => $slug,
        ];
    }
}
