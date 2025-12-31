<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'customer_id' => Customer::factory(),
            'package_id' => Package::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->boolean(70) ? fake()->sentence() : null,
        ];
    }
}
