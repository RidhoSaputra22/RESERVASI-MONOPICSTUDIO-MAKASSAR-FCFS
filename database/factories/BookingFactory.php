<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Photographer;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'package_id' => Package::factory(),
            'photographer_id' => fake()->boolean(80) ? Photographer::factory() : null,
            'studio_id' => fake()->boolean(80) ? Studio::factory() : null,
            'scheduled_at' => fake()->optional()->dateTimeBetween('+1 day', '+1 month'),
            'snap_token' => fake()->optional()->uuid(),
            'status' => fake()->randomElement(BookingStatus::cases()),
            'code' => null,
        ];
    }
}
